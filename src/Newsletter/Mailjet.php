<?php

namespace App\Newsletter;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * L'API REST de Mailjet, pour ce que Symfony Mailer ne sait pas faire : tenir
 * les listes de contacts, et envoyer la lettre mensuelle par lots.
 *
 * Portage de `application/helpers/email_helper.php`, appel pour appel — le
 * legacy passait par le SDK `mailjet/mailjet-apiv3-php`, qui n'est qu'une
 * surcouche de ces mêmes routes : un client HTTP suffit, sans dépendance de
 * plus. Les courriels transactionnels (mot de passe, demande de compte) restent
 * sur `MAILER_DSN` ; ceux-ci passent ici parce qu'ils s'appuient sur des objets
 * qui n'existent que chez Mailjet (listes, modèle de bienvenue).
 *
 * **Sans clés, tout est sans effet** ({@see self::estConfigure()}) : c'est l'état
 * voulu en développement et en préproduction. Des clés de production posées sur
 * une copie de la base y abonneraient des adresses d'essai aux vraies listes.
 *
 * Un contact se désigne par son adresse dans l'URL : l'API v3 l'accepte partout
 * à la place de l'identifiant numérique, et le legacy s'en servait déjà ainsi.
 */
class Mailjet
{
    /** Liste « Newsletter principale » (`general` dans la base du legacy). */
    public const LISTE_GENERALE = 25834;

    /** Liste « Newsletter votes ». */
    public const LISTE_VOTES = 47010;

    /** Modèle « Bienvenue » conçu dans l'interface Mailjet — son contenu n'est pas dans le dépôt. */
    public const MODELE_BIENVENUE = 2826349;

    /** Plafond de l'API d'envoi v3.1 par requête. */
    public const MESSAGES_PAR_LOT = 50;

    private const API = 'https://api.mailjet.com';

    private const EXPEDITEUR = ['Email' => 'info@datan.fr', 'Name' => 'Datan'];

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        #[Autowire(env: 'MAILJET_API_KEY')] private readonly string $cle,
        #[Autowire(env: 'MAILJET_API_SECRET')] private readonly string $secret,
    ) {
    }

    public function estConfigure(): bool
    {
        return $this->cle !== '' && $this->secret !== '';
    }

    /**
     * Crée le contact s'il n'existe pas, puis l'inscrit aux listes.
     *
     * `addforce` et non `addnoforce` : comme le legacy, une réinscription
     * explicite annule une désinscription passée chez Mailjet.
     *
     * @param list<int> $listes
     */
    public function inscrire(string $email, array $listes): void
    {
        // 400 quand le contact existe déjà (MJ18) : c'est le cas d'une
        // réinscription, pas une erreur.
        $this->appeler('POST', '/v3/REST/contact', ['Email' => $email], [400]);
        $this->gererListes($email, $listes, 'addforce');
    }

    /** @param list<int> $listes */
    public function desinscrire(string $email, array $listes): void
    {
        // 404 : contact inconnu de Mailjet — inscrit avant la synchronisation,
        // ou supprimé de son côté. Il n'y a rien à retirer.
        $this->gererListes($email, $listes, 'remove', [404]);
    }

    /**
     * Listes du contact et leur état, par identifiant de liste. Null si Mailjet
     * ne connaît pas l'adresse.
     *
     * @return array<int, array{IsUnsub: bool, IsActive: bool}>|null
     */
    public function listesDuContact(string $email): ?array
    {
        $reponse = $this->appeler('GET', '/v3/REST/contact/' . rawurlencode($email) . '/getcontactslists', null, [404]);

        if ($reponse === null) {
            return null;
        }

        $listes = [];
        foreach ($reponse['Data'] ?? [] as $liste) {
            $listes[(int) $liste['ListID']] = [
                'IsUnsub' => (bool) $liste['IsUnsub'],
                'IsActive' => (bool) $liste['IsActive'],
            ];
        }

        return $listes;
    }

    /**
     * Courriel de bienvenue, composé chez Mailjet à partir du modèle. Le modèle
     * attend `email` et `email_encode`, ce dernier pour bâtir le lien de
     * désinscription `/newsletter/delete/{email}`.
     */
    public function envoyerBienvenue(string $email): void
    {
        $this->envoyer([[
            'From' => self::EXPEDITEUR,
            'To' => [['Email' => $email]],
            'Subject' => 'Bienvenue à la newsletter',
            'TemplateID' => self::MODELE_BIENVENUE,
            'TemplateLanguage' => true,
            'Variables' => ['email' => $email, 'email_encode' => urlencode($email)],
        ]]);
    }

    /**
     * Le même HTML à chaque destinataire, par lots de {@see self::MESSAGES_PAR_LOT}.
     *
     * `TemplateLanguage` reste vrai comme au legacy : c'est lui qui fait
     * remplacer `[[EMAIL_TO]]` par l'adresse du destinataire, dans le lien
     * « gérer vos abonnements » du pied de lettre.
     *
     * L'API traite chaque message d'un lot séparément et rend un statut par
     * message, même quand elle répond 400 parce que l'un d'eux a échoué : le
     * corps fait foi, pas le code HTTP.
     *
     * @param list<string> $destinataires
     *
     * @return array{envoyes: int, echecs: array<string, string>} les échecs par adresse
     */
    public function envoyerATous(array $destinataires, string $sujet, string $html): array
    {
        $envoyes = 0;
        $echecs = [];

        foreach (array_chunk($destinataires, self::MESSAGES_PAR_LOT) as $lot) {
            $messages = array_map(static fn (string $email) => [
                'From' => self::EXPEDITEUR,
                'To' => [['Email' => $email]],
                'Subject' => $sujet,
                'HTMLPart' => $html,
                'TemplateLanguage' => true,
            ], $lot);

            try {
                $statuts = $this->envoyer($messages, [400]);
            } catch (\RuntimeException $e) {
                // Lot refusé en bloc (authentification, quota, réseau) : on le
                // compte en échec et on tente le suivant plutôt que de laisser
                // la moitié des abonnés sans savoir lesquels.
                foreach ($lot as $email) {
                    $echecs[$email] = $e->getMessage();
                }

                continue;
            }

            foreach ($lot as $i => $email) {
                $statut = $statuts[$i] ?? null;

                if (($statut['Status'] ?? null) === 'success') {
                    ++$envoyes;
                } else {
                    $echecs[$email] = $statut['Errors'][0]['ErrorMessage'] ?? 'statut absent de la réponse';
                }
            }
        }

        return ['envoyes' => $envoyes, 'echecs' => $echecs];
    }

    /**
     * @param list<array<string, mixed>> $messages
     * @param list<int>                  $codesAdmis
     *
     * @return list<array<string, mixed>> le statut de chaque message, dans l'ordre
     */
    private function envoyer(array $messages, array $codesAdmis = []): array
    {
        $reponse = $this->appeler('POST', '/v3.1/send', ['Messages' => $messages], $codesAdmis, true);

        return $reponse['Messages'] ?? [];
    }

    /**
     * @param list<int> $listes
     * @param list<int> $codesAdmis
     */
    private function gererListes(string $email, array $listes, string $action, array $codesAdmis = []): void
    {
        if ($listes === []) {
            return;
        }

        $this->appeler(
            'POST',
            '/v3/REST/contact/' . rawurlencode($email) . '/managecontactslists',
            ['ContactsLists' => array_map(static fn (int $liste) => ['Action' => $action, 'ListID' => $liste], $listes)],
            $codesAdmis,
        );
    }

    /**
     * @param array<string, mixed>|null $corps
     * @param list<int>                 $codesAdmis codes d'erreur attendus, rendus null sans lever
     * @param bool                      $lireCorpsEnErreur lit le corps d'une réponse admise au lieu de rendre null
     *
     * @return array<string, mixed>|null
     */
    private function appeler(string $methode, string $chemin, ?array $corps, array $codesAdmis = [], bool $lireCorpsEnErreur = false): ?array
    {
        if (!$this->estConfigure()) {
            throw new \LogicException('Mailjet n\'est pas configuré (MAILJET_API_KEY, MAILJET_API_SECRET).');
        }

        $options = ['auth_basic' => [$this->cle, $this->secret], 'timeout' => 30];
        if ($corps !== null) {
            $options['json'] = $corps;
        }

        $reponse = $this->httpClient->request($methode, self::API . $chemin, $options);
        $code = $reponse->getStatusCode();

        if ($code >= 200 && $code < 300) {
            return $reponse->toArray(false);
        }

        if (\in_array($code, $codesAdmis, true)) {
            return $lireCorpsEnErreur ? $reponse->toArray(false) : null;
        }

        $detail = mb_substr($reponse->getContent(false), 0, 500);
        $this->logger->error('Mailjet {methode} {chemin} : {code}', ['methode' => $methode, 'chemin' => $chemin, 'code' => $code, 'detail' => $detail]);

        throw new \RuntimeException(sprintf('Mailjet %s %s a répondu %d : %s', $methode, $chemin, $code, $detail));
    }
}
