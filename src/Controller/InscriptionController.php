<?php

namespace App\Controller;

use App\Entity\DemandeCompteDepute;
use App\Entity\Utilisateur;
use App\Repository\DemandeCompteDeputeRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Inscription, portée de `Users::register`.
 *
 * Deux entrées sous la même adresse, comme le legacy :
 *  - `/register` ouvre un compte **lecteur** (le `type` vide de l'origine) :
 *    ni rédaction, ni député, seul l'accès à « mon compte ». Les lecteurs du
 *    legacy n'ont pas été repris — un ancien lecteur recrée son compte, c'est
 *    assumé ;
 *  - `/register/{token}` termine le flux de compte **député** : le jeton, posé
 *    à l'approbation de la demande (voir Admin\DemandeCompteController), laisse
 *    le député créer lui-même son compte et choisir son mot de passe. Le legacy
 *    résolvait le jeton dans `users_mp_link` ; ici il vit sur
 *    `demande_compte_depute`, donc en aval d'une relecture humaine.
 *
 * Pages **publiques** à formulaire de session : jeton CSRF, jamais de cache.
 */
class InscriptionController extends AbstractController
{
    /**
     * Plancher de mot de passe pour un lecteur. Le legacy n'en imposait aucun
     * (défaut) ; la commande d'ouverture de compte de la rédaction exige douze
     * caractères. Huit est un compromis raisonnable pour un compte lecteur.
     */
    private const MIN_MOT_DE_PASSE = 8;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UtilisateurRepository $utilisateurs,
        private readonly UserPasswordHasherInterface $hacheur,
        private readonly MailerInterface $courrielleur,
    ) {
    }

    #[Route('/register', name: 'inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $requete): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('apres_connexion');
        }

        if ($requete->isMethod('POST')) {
            return $this->inscrireLecteur($requete);
        }

        return $this->sansCache($this->render('inscription/index.html.twig', [
            'depute' => null,
            'valeurs' => [],
        ]));
    }

    #[Route('/register/{token}', name: 'inscription_depute', methods: ['GET', 'POST'])]
    public function inscriptionDepute(string $token, Request $requete, DemandeCompteDeputeRepository $demandes): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('apres_connexion');
        }

        // Jeton inconnu, déjà consommé ou demande non approuvée : 404, comme le
        // `show_404()` du legacy sur un lien d'activation périmé.
        $demande = $demandes->parToken($token);

        if ($demande === null) {
            throw $this->createNotFoundException('Lien d\'activation inconnu ou expiré.');
        }

        $depute = $demande->getDepute();

        // Un compte a pu naître entre-temps (commande, seconde demande) : on
        // annule le jeton et on renvoie à la connexion, sans fabriquer de doublon.
        if ($this->utilisateurs->findOneBy(['depute' => $depute]) !== null) {
            $demande->setToken(null);
            $this->entityManager->flush();
            $this->addFlash('erreur', 'Un compte Datan existe déjà pour vous. Connectez-vous ci-dessous.');

            return $this->redirectToRoute('connexion');
        }

        if ($requete->isMethod('POST')) {
            return $this->activerCompteDepute($demande, $requete);
        }

        return $this->sansCache($this->render('inscription/index.html.twig', [
            'depute' => $depute,
            'token' => $token,
            'valeurs' => ['email' => $demande->getEmail()],
        ]));
    }

    /**
     * Création d'un compte lecteur. Champs et règles repris de `Users::register`
     * (branche `type = ''`) : nom, code postal, e-mail, pseudo, mot de passe.
     */
    private function inscrireLecteur(Request $requete): Response
    {
        $valeurs = [
            'name' => trim((string) $requete->request->get('name')),
            'zipcode' => trim((string) $requete->request->get('zipcode')),
            'email' => trim((string) $requete->request->get('email')),
            'username' => trim((string) $requete->request->get('username')),
        ];
        $motDePasse = (string) $requete->request->get('password');
        $confirmation = (string) $requete->request->get('password2');

        $erreurs = $this->validerJeton($requete, 'inscription');
        $erreurs = array_merge($erreurs, $this->validerChampsCommuns($valeurs, $motDePasse, $confirmation));

        if ($valeurs['zipcode'] === '' || !ctype_digit($valeurs['zipcode'])) {
            $erreurs[] = 'Le code postal doit être un nombre.';
        }

        if ($erreurs !== []) {
            return $this->reafficher(null, null, $valeurs, $erreurs);
        }

        $lecteur = new Utilisateur();
        $lecteur->setIdentifiant($valeurs['username']);
        $lecteur->setNom($valeurs['name']);
        $lecteur->setEmail($valeurs['email']);
        $lecteur->setCodePostal($valeurs['zipcode']);
        // Le marqueur qui empêche le repli sur ROLE_REDACTEUR : un lecteur porte
        // ROLE_LECTEUR explicitement (Utilisateur::getRoles).
        $lecteur->setRoles([Utilisateur::ROLE_LECTEUR]);
        $lecteur->setPassword($this->hacheur->hashPassword($lecteur, $motDePasse));

        $this->entityManager->persist($lecteur);
        $this->entityManager->flush();

        $this->confirmerParCourriel($valeurs['email'], $valeurs['name']);

        $this->addFlash('succes', 'Votre compte a bien été créé. Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('inscription');
    }

    /**
     * Fin du flux député : le jeton est valide, le député pose son pseudo et son
     * mot de passe. Le compte porte le rattachement au député (donc ROLE_DEPUTE
     * exclusif), et le jeton est annulé — un usage unique.
     */
    private function activerCompteDepute(DemandeCompteDepute $demande, Request $requete): Response
    {
        $depute = $demande->getDepute();
        $valeurs = [
            'name' => $depute->getFirstname() . ' ' . $depute->getLastname(),
            'email' => $demande->getEmail(),
            'username' => trim((string) $requete->request->get('username')),
        ];
        $motDePasse = (string) $requete->request->get('password');
        $confirmation = (string) $requete->request->get('password2');

        $erreurs = $this->validerJeton($requete, 'inscription');
        $erreurs = array_merge($erreurs, $this->validerPseudo($valeurs['username']));
        $erreurs = array_merge($erreurs, $this->validerMotDePasse($motDePasse, $confirmation));

        if ($erreurs !== []) {
            return $this->reafficher($depute, $demande->getToken(), $valeurs, $erreurs);
        }

        $utilisateur = new Utilisateur();
        $utilisateur->setIdentifiant($valeurs['username']);
        $utilisateur->setNom($valeurs['name']);
        $utilisateur->setEmail($valeurs['email']);
        $utilisateur->setDepute($depute);
        $utilisateur->setRoles([]);
        $utilisateur->setPassword($this->hacheur->hashPassword($utilisateur, $motDePasse));

        $this->entityManager->persist($utilisateur);
        $demande->setToken(null);
        $this->entityManager->flush();

        $this->addFlash('succes', 'Votre compte de député est créé. Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('connexion');
    }

    /**
     * Règles communes aux deux inscriptions : nom, e-mail unique, pseudo, mot de
     * passe. Le code postal, propre au lecteur, est vérifié à part.
     *
     * @param array{name: string, email: string, username: string, zipcode?: string} $valeurs
     *
     * @return list<string>
     */
    private function validerChampsCommuns(array $valeurs, string $motDePasse, string $confirmation): array
    {
        $erreurs = [];

        if ($valeurs['name'] === '') {
            $erreurs[] = 'Le nom est obligatoire.';
        }

        if ($valeurs['email'] === '' || !filter_var($valeurs['email'], \FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'Merci de saisir une adresse électronique valide.';
        } elseif ($this->utilisateurs->findOneBy(['email' => $valeurs['email']]) !== null) {
            $erreurs[] = 'Cette adresse électronique est déjà utilisée.';
        }

        $erreurs = array_merge($erreurs, $this->validerPseudo($valeurs['username']));

        return array_merge($erreurs, $this->validerMotDePasse($motDePasse, $confirmation));
    }

    /**
     * Pseudo : jusqu'à dix caractères alphanumériques, et libre. Contraintes
     * reprises de `check_username_exists|max_length[10]|alpha_numeric`.
     *
     * @return list<string>
     */
    private function validerPseudo(string $pseudo): array
    {
        if ($pseudo === '') {
            return ['Le pseudo est obligatoire.'];
        }

        if (mb_strlen($pseudo) > 10 || !ctype_alnum($pseudo)) {
            return ['Le pseudo doit faire au plus dix caractères, lettres et chiffres uniquement.'];
        }

        if ($this->utilisateurs->findOneBy(['identifiant' => $pseudo]) !== null) {
            return ['Ce pseudo est déjà pris. Merci d\'en choisir un autre.'];
        }

        return [];
    }

    /**
     * @return list<string>
     */
    private function validerMotDePasse(string $motDePasse, string $confirmation): array
    {
        if (mb_strlen($motDePasse) < self::MIN_MOT_DE_PASSE) {
            return [sprintf('Le mot de passe doit faire au moins %d caractères.', self::MIN_MOT_DE_PASSE)];
        }

        if ($motDePasse !== $confirmation) {
            return ['Les deux mots de passe ne correspondent pas.'];
        }

        return [];
    }

    /**
     * @return list<string>
     */
    private function validerJeton(Request $requete, string $id): array
    {
        if (!$this->isCsrfTokenValid($id, (string) $requete->request->get('_token'))) {
            return ['Votre session a expiré, merci de renvoyer le formulaire.'];
        }

        return [];
    }

    /**
     * @param array<string, string> $valeurs
     * @param list<string>          $erreurs
     */
    private function reafficher(?object $depute, ?string $token, array $valeurs, array $erreurs): Response
    {
        return $this->sansCache($this->render('inscription/index.html.twig', [
            'depute' => $depute,
            'token' => $token,
            'valeurs' => $valeurs,
            'erreurs' => $erreurs,
        ], new Response('', Response::HTTP_UNPROCESSABLE_ENTITY)));
    }

    private function confirmerParCourriel(string $adresse, string $nom): void
    {
        // Courriel de confirmation, comme le legacy. Avec MAILER_DSN=null:// en
        // local, l'envoi est un no-op : le flux est complet, jamais d'appel
        // Mailjet en dur — le transport est branché au déploiement.
        $courriel = (new Email())
            ->from(new Address('info@datan.fr', 'Datan'))
            ->to($adresse)
            ->subject('Votre compte Datan a été créé')
            ->text(sprintf(
                "Bonjour %s,\n\nVotre compte Datan a bien été créé. Vous pouvez vous connecter sur %s.\n\nL'équipe de Datan",
                $nom,
                $this->generateUrl('connexion', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ));

        $this->courrielleur->send($courriel);
    }

    private function sansCache(Response $reponse): Response
    {
        $reponse->setPrivate();
        $reponse->headers->addCacheControlDirective('no-store', true);

        return $reponse;
    }
}
