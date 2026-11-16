<?php

namespace App\Controller;

use App\Entity\ReinitialisationMotDePasse;
use App\Repository\ReinitialisationMotDePasseRepository;
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
 * Mot de passe oublié, porté de `Users::password_lost_request` et
 * `Users::password_lost_change`.
 *
 * `/password` demande une adresse et envoie un lien de réinitialisation ;
 * `/password/{token}` accueille le lien et pose le nouveau mot de passe. Le
 * courriel part par MAILER_DSN (`null://` en local : le flux est complet, rien
 * n'est envoyé, jamais d'appel Mailjet en dur — le transport est branché au
 * déploiement).
 *
 * Pages **publiques** à formulaire de session : jeton CSRF, jamais de cache.
 */
class MotDePasseController extends AbstractController
{
    private const MIN_MOT_DE_PASSE = 8;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UtilisateurRepository $utilisateurs,
        private readonly ReinitialisationMotDePasseRepository $reinitialisations,
        private readonly UserPasswordHasherInterface $hacheur,
        private readonly MailerInterface $courrielleur,
    ) {
    }

    #[Route('/password', name: 'mot_de_passe_oubli', methods: ['GET', 'POST'])]
    public function demande(Request $requete): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('apres_connexion');
        }

        if ($requete->isMethod('POST')) {
            return $this->envoyerLien($requete);
        }

        return $this->sansCache($this->render('mot_de_passe/demande.html.twig'));
    }

    #[Route('/password/{token}', name: 'mot_de_passe_reinitialiser', methods: ['GET', 'POST'])]
    public function reinitialiser(string $token, Request $requete): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('apres_connexion');
        }

        $reinitialisation = $this->reinitialisations->parToken($token);

        // Jeton inconnu : 404, comme le `show_404()` du legacy sur un lien vide.
        if ($reinitialisation === null) {
            throw $this->createNotFoundException('Lien de réinitialisation inconnu.');
        }

        // Passé une heure, le lien ne vaut plus : on le purge et on renvoie à la
        // demande, exactement comme le legacy (« Ce lien ne fonctionne plus »).
        if (!$reinitialisation->estValide()) {
            $this->entityManager->remove($reinitialisation);
            $this->entityManager->flush();
            $this->addFlash('erreur', 'Ce lien ne fonctionne plus. Veuillez en redemander un nouveau.');

            return $this->redirectToRoute('mot_de_passe_oubli');
        }

        if ($requete->isMethod('POST')) {
            return $this->changer($reinitialisation, $requete);
        }

        return $this->sansCache($this->render('mot_de_passe/reinitialiser.html.twig'));
    }

    private function envoyerLien(Request $requete): Response
    {
        if (!$this->isCsrfTokenValid('mot_de_passe_oubli', (string) $requete->request->get('_token'))) {
            $this->addFlash('erreur', 'Votre session a expiré, merci de renvoyer votre demande.');

            return $this->redirectToRoute('mot_de_passe_oubli');
        }

        $email = trim((string) $requete->request->get('email'));
        $utilisateur = filter_var($email, \FILTER_VALIDATE_EMAIL)
            ? $this->utilisateurs->findOneBy(['email' => $email])
            : null;

        if ($utilisateur !== null) {
            // Un seul lien valable à la fois : on purge les jetons pendants avant
            // d'en poser un neuf.
            $this->reinitialisations->purgerPour($utilisateur);

            $reinitialisation = new ReinitialisationMotDePasse();
            $reinitialisation->setUtilisateur($utilisateur);
            $reinitialisation->setToken(bin2hex(random_bytes(32)));

            $this->entityManager->persist($reinitialisation);
            $this->entityManager->flush();

            $this->envoyerCourriel($utilisateur->getEmail(), $utilisateur->getNom(), $reinitialisation->getToken());
        }

        // Message neutre, qu'un compte existe ou non : le legacy révélait
        // l'existence de l'adresse (« Nous ne trouvons pas votre email »),
        // c'est-à-dire de qui a un compte — défaut d'énumération corrigé.
        $this->addFlash('succes', 'Si un compte correspond à cette adresse, un courriel de réinitialisation vient de vous être envoyé. Le lien est valable une heure.');

        return $this->redirectToRoute('mot_de_passe_oubli');
    }

    private function changer(ReinitialisationMotDePasse $reinitialisation, Request $requete): Response
    {
        if (!$this->isCsrfTokenValid('mot_de_passe_reinitialiser', (string) $requete->request->get('_token'))) {
            $this->addFlash('erreur', 'Votre session a expiré, merci de recommencer.');

            return $this->redirectToRoute('mot_de_passe_reinitialiser', ['token' => $reinitialisation->getToken()]);
        }

        $nouveau = (string) $requete->request->get('new');
        $confirmation = (string) $requete->request->get('new_confirmation');

        if (mb_strlen($nouveau) < self::MIN_MOT_DE_PASSE) {
            $this->addFlash('erreur', sprintf('Le mot de passe doit faire au moins %d caractères.', self::MIN_MOT_DE_PASSE));

            return $this->redirectToRoute('mot_de_passe_reinitialiser', ['token' => $reinitialisation->getToken()]);
        }

        if ($nouveau !== $confirmation) {
            $this->addFlash('erreur', 'Les deux mots de passe ne correspondent pas.');

            return $this->redirectToRoute('mot_de_passe_reinitialiser', ['token' => $reinitialisation->getToken()]);
        }

        $utilisateur = $reinitialisation->getUtilisateur();
        $utilisateur->setPassword($this->hacheur->hashPassword($utilisateur, $nouveau));

        // Jeton à usage unique : on le supprime une fois servi. Le legacy le
        // laissait valable une heure de plus (réutilisable) — défaut corrigé.
        $this->entityManager->remove($reinitialisation);
        $this->entityManager->flush();

        $this->addFlash('succes', 'Votre mot de passe a été changé. Vous pouvez maintenant vous connecter.');

        return $this->redirectToRoute('connexion');
    }

    private function envoyerCourriel(string $adresse, string $nom, string $token): void
    {
        $lien = $this->generateUrl('mot_de_passe_reinitialiser', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        $courriel = (new Email())
            ->from(new Address('info@datan.fr', 'Datan'))
            ->to($adresse)
            ->subject('Changez votre mot de passe Datan')
            ->text(sprintf(
                "Bonjour %s,\n\nVous avez demandé à réinitialiser votre mot de passe Datan. Suivez ce lien, valable une heure :\n%s\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez ce message.\n\nL'équipe de Datan",
                $nom,
                $lien,
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
