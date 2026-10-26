<?php

namespace App\Controller;

use App\Http\RechauffeurCache;
use App\Newsletter\Mailjet;
use App\Newsletter\SynchroMailjet;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Inscription à la newsletter (Newsletter::register de l'application
 * CodeIgniter d'origine, vue application/views/newsletter/index.php).
 *
 * L'inscription a été **cassée sur le site du 24 mars au 31 juillet 2026** : le
 * contrôleur `Legacy_api.php`, qui servait `/api/newsletter/create_newsletter`,
 * avait été supprimé en laissant sa route et les deux formulaires (modale +
 * page) qui postent dessus — chaque tentative tombait en 404 et l'internaute
 * recevait « Vous êtes sans doute déjà inscrit ! ». Le site l'a rétablie le
 * 31 juillet (commit 85c67bbf : route vers `Newsletter::create`, 405 hors POST,
 * `false` sur une adresse invalide, puis `create_newsletter()`) — c'est le
 * comportement que {@see self::creer()} reproduit.
 *
 * La base est la source de vérité des abonnés ; chaque changement y est écrit
 * d'abord, puis reporté chez Mailjet par le consommateur Messenger
 * ({@see SynchroMailjet}) : listes de contacts et courriel de bienvenue, comme
 * le faisait le legacy, mais hors de la requête.
 *
 * Les pages d'abonnement (`/newsletter/edit/{email}`, `/newsletter/delete/{email}`)
 * sont des **adresses publiées** : chaque lettre envoyée depuis des années
 * porte la première dans son pied, le courriel de bienvenue la seconde. Elles
 * sont aussi le seul moyen de se désabonner de la lettre des votes, envoyée
 * hors des campagnes Mailjet et donc sans lien de désinscription de Mailjet.
 */
class NewsletterController extends AbstractController
{
    /** Page immuable hors déploiement, comme les pages éditoriales. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /**
     * Les listes proposées sur la page d'abonnement : nom du champ du formulaire
     * (celui du legacy), colonne de la table, liste Mailjet.
     */
    private const LISTES = [
        ['libelle' => 'Newsletter principale', 'champ' => 'general', 'colonne' => 'liste_generale', 'mailjet' => Mailjet::LISTE_GENERALE],
        ['libelle' => 'Newsletter votes', 'champ' => 'votes', 'colonne' => 'liste_votes', 'mailjet' => Mailjet::LISTE_VOTES],
    ];

    public function __construct(
        private readonly Connection $connection,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * La route accepte le POST : sans JavaScript, les deux formulaires postent
     * ici (`form_open('newsletter')`), et le legacy se contente de re-rendre
     * la page. Avec JavaScript, `main.min.js` intercepte l'envoi.
     */
    #[Route('/newsletter', name: 'newsletter', methods: ['GET', 'POST'])]
    public function inscription(): Response
    {
        $response = $this->render('newsletter/index.html.twig', [
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Newsletter', 'url' => $this->generateUrl('newsletter')],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Point d'entrée AJAX des deux formulaires, à l'adresse historique — c'est
     * `main.min.js`, repris tel quel du site, qui la porte en dur. La réponse
     * est un simple booléen JSON : `false` déclenche le message d'échec côté
     * client, `true` le message de félicitations.
     *
     * Pas de jeton CSRF, comme l'origine : toutes les pages publiques passent
     * par le cache HTTP partagé, qui servirait le jeton du premier visiteur à
     * tous les autres — l'inscription échouerait alors pour tout le monde.
     * L'enjeu se limite à des insertions d'adresses indésirables, que l'unicité
     * et la validation contiennent.
     */
    #[Route('/api/newsletter/create_newsletter', name: 'newsletter_creer', methods: ['POST'])]
    public function creer(Request $request): JsonResponse
    {
        $email = trim((string) $request->request->get('email', ''));

        // Le legacy s'en remettait au `type="email"` du navigateur et insérait
        // tel quel ce qui arrivait ; on refuse ici ce qui n'est pas une
        // adresse, le client affichant le même message d'échec.
        if ($email === '' || filter_var($email, \FILTER_VALIDATE_EMAIL) === false) {
            return new JsonResponse(false);
        }

        $existe = $this->connection->fetchOne(
            'SELECT 1 FROM newsletter WHERE email = :email',
            ['email' => $email],
        );

        if ($existe !== false) {
            return new JsonResponse(false);
        }

        try {
            // Toute nouvelle adresse est abonnée aux deux listes, comme sur le
            // site. Des entiers, jamais des booléens : mysqli lierait une
            // chaîne vide.
            $this->connection->insert('newsletter', [
                'email' => $email,
                'liste_generale' => 1,
                'liste_votes' => 1,
                'inscrit_le' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Deux envois simultanés de la même adresse : le second perd.
            return new JsonResponse(false);
        }

        $this->bus->dispatch(new SynchroMailjet(
            $email,
            ajouter: array_column(self::LISTES, 'mailjet'),
            bienvenue: true,
        ));

        return new JsonResponse(true);
    }

    /**
     * Gestion des abonnements d'une adresse (`Newsletter::edit`).
     *
     * L'adresse dans l'URL suffit à modifier les abonnements, sans compte ni
     * jeton : c'est le contrat du lien envoyé depuis des années, qui ne porte
     * rien d'autre. Le jeton CSRF, que le legacy posait aussi, protège seulement
     * contre un formulaire tiers qui posterait à l'insu de l'internaute.
     *
     * Le lien du pied de lettre arrive avec un `@` en clair (`[[EMAIL_TO]]`),
     * celui du bouton « Retour » encodé en `%40` : le routeur décode l'un et
     * l'autre en la même adresse. Le legacy redirigeait la première forme vers
     * la seconde, contrainte de CodeIgniter qui ne nous concerne pas.
     */
    #[Route('/newsletter/edit/{email}', name: 'newsletter_modifier', methods: ['GET', 'POST'])]
    public function modifier(string $email, Request $request): Response
    {
        $abonne = $this->connection->fetchAssociative(
            'SELECT email, liste_generale, liste_votes FROM newsletter WHERE email = :email',
            ['email' => $email],
        );

        if ($abonne === false) {
            throw $this->createNotFoundException('Adresse non inscrite à la newsletter.');
        }

        if (!$request->isMethod('POST')) {
            return $this->sansCache($this->render('newsletter/modifier.html.twig', [
                'abonne' => $abonne,
                'listes' => self::LISTES,
                'session_expiree' => false,
            ]));
        }

        if (!$this->isCsrfTokenValid('newsletter_modifier', (string) $request->request->get('_token'))) {
            return $this->sansCache($this->render('newsletter/modifier.html.twig', [
                'abonne' => $abonne,
                'listes' => self::LISTES,
                'session_expiree' => true,
            ]));
        }

        $ajouter = [];
        $retirer = [];
        $colonnes = [];

        foreach (self::LISTES as $liste) {
            // Case à cocher : présente (« on ») ou absente du formulaire.
            $voulu = $request->request->has($liste['champ']) ? 1 : 0;

            if ($voulu !== (int) $abonne[$liste['colonne']]) {
                $colonnes[$liste['colonne']] = $voulu;
                if ($voulu === 1) {
                    $ajouter[] = $liste['mailjet'];
                } else {
                    $retirer[] = $liste['mailjet'];
                }
            }
        }

        if ($colonnes !== []) {
            $colonnes['modifie_le'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            $this->connection->update('newsletter', $colonnes, ['email' => $abonne['email']]);
            $this->bus->dispatch(new SynchroMailjet($abonne['email'], $ajouter, $retirer));
        }

        return $this->sansCache($this->render('newsletter/modifier_succes.html.twig', [
            'abonne' => $abonne,
        ]));
    }

    /**
     * Désinscription de toutes les listes (`Newsletter::delete`) : la ligne est
     * supprimée, comme au legacy.
     *
     * Un GET qui supprime, parce que c'est un lien de courriel : il doit agir en
     * un clic. La page répond de même pour une adresse inconnue — déjà
     * désinscrite, ou jamais inscrite —, et la retire quand même de Mailjet,
     * qui peut la connaître sans que la base la connaisse encore.
     *
     * Deux défauts du legacy corrigés : il ne retirait l'adresse que de la liste
     * principale chez Mailjet, pas de celle des votes ; et il affichait l'adresse
     * encodée telle que reçue (`jean%40exemple.fr`).
     */
    #[Route('/newsletter/delete/{email}', name: 'newsletter_desinscrire', methods: ['GET'])]
    public function desinscrire(string $email): Response
    {
        $this->connection->delete('newsletter', ['email' => $email]);
        $this->bus->dispatch(new SynchroMailjet($email, retirer: array_column(self::LISTES, 'mailjet')));

        return $this->sansCache($this->render('newsletter/desinscription.html.twig', [
            'email' => $email,
        ]));
    }

    /**
     * Pages personnelles : l'adresse y est affichée, aucune ne doit entrer dans
     * le cache partagé.
     */
    private function sansCache(Response $reponse): Response
    {
        $reponse->setPrivate();
        $reponse->headers->addCacheControlDirective('no-store', true);

        return $reponse;
    }
}
