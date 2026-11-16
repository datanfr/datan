<?php

namespace App\Controller\Admin;

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
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Traitement des demandes de compte des députés (`/demande-compte-depute`).
 *
 * L'application d'origine n'avait pas d'écran d'administration : le député
 * recevait par courriel un lien d'activation et créait lui-même son compte via
 * `/register/{token}`. Ce portage réintroduit ce lien, mais **en aval d'une
 * relecture** : un administrateur approuve d'abord la demande, ce qui émet le
 * jeton d'activation ; le lien `/register/{token}` part alors à l'adresse
 * institutionnelle (par courriel au déploiement, MAILER_DSN) et s'affiche ici
 * pour transmission manuelle. Le contrôle par l'adresse `@assemblee-nationale.fr`
 * tient toujours — c'est là qu'on envoie le lien — et le député choisit
 * lui-même son mot de passe.
 *
 * Réservé aux administrateurs : émettre un accès de connexion est un acte plus
 * sensible que rédiger un décryptage.
 */
#[Route('/admin/demandes-comptes')]
#[IsGranted(Utilisateur::ROLE_ADMIN)]
class DemandeCompteController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DemandeCompteDeputeRepository $demandes,
        private readonly UtilisateurRepository $utilisateurs,
        private readonly MailerInterface $courrielleur,
    ) {
    }

    #[Route('', name: 'admin_demande_compte_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/demande_compte/index.html.twig', [
            'demandes' => $this->demandes->enAttente(),
        ]);
    }

    #[Route('/{id}/approuver', name: 'admin_demande_compte_approuver', methods: ['POST'])]
    public function approuver(DemandeCompteDepute $demande, Request $requete): Response
    {
        if (!$this->jetonValide($demande, $requete)) {
            return $this->redirectToRoute('admin_demande_compte_index');
        }

        $depute = $demande->getDepute();

        // Un compte a pu être ouvert entre-temps (commande, autre demande) : on
        // ne fabrique pas de doublon, on classe la demande.
        if ($this->utilisateurs->findOneBy(['depute' => $depute]) !== null) {
            $this->classer($demande, DemandeCompteDepute::REFUSEE);
            $this->addFlash('erreur', sprintf(
                '%s %s a déjà un compte : la demande a été classée sans en créer un second.',
                $depute->getFirstname(),
                $depute->getLastname(),
            ));

            return $this->redirectToRoute('admin_demande_compte_index');
        }

        // On émet le jeton d'activation (l'ancien `users_mp_link`), le compte
        // n'étant créé qu'au bout du lien, par le député lui-même.
        $token = bin2hex(random_bytes(50));
        $demande->setToken($token);
        $this->classer($demande, DemandeCompteDepute::APPROUVEE);

        $lien = $this->generateUrl('inscription_depute', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
        $this->envoyerLien($demande->getEmail(), $depute->getFirstname() . ' ' . $depute->getLastname(), $lien);

        // On affiche le lien pour transmission manuelle : en local (MAILER_DSN
        // null://) rien n'est envoyé, et même au déploiement l'administrateur
        // garde le lien sous les yeux pour le confier à l'adresse de l'Assemblée.
        $this->addFlash('succes', sprintf(
            'Demande approuvée pour %s %s. Lien d\'activation à transmettre à %s : %s',
            $depute->getFirstname(),
            $depute->getLastname(),
            $demande->getEmail(),
            $lien,
        ));

        return $this->redirectToRoute('admin_demande_compte_index');
    }

    #[Route('/{id}/refuser', name: 'admin_demande_compte_refuser', methods: ['POST'])]
    public function refuser(DemandeCompteDepute $demande, Request $requete): Response
    {
        if (!$this->jetonValide($demande, $requete)) {
            return $this->redirectToRoute('admin_demande_compte_index');
        }

        $depute = $demande->getDepute();
        $this->classer($demande, DemandeCompteDepute::REFUSEE);

        $this->addFlash('succes', sprintf(
            'Demande de %s %s refusée.',
            $depute->getFirstname(),
            $depute->getLastname(),
        ));

        return $this->redirectToRoute('admin_demande_compte_index');
    }

    /**
     * Jeton CSRF valide et demande encore à traiter — sinon un message et un
     * retour à la liste. Le jeton est propre à la demande, pour qu'un bouton
     * copié ne serve pas sur une autre.
     */
    private function jetonValide(DemandeCompteDepute $demande, Request $requete): bool
    {
        if (!$this->isCsrfTokenValid('traiter_demande_' . $demande->getId(), (string) $requete->request->get('_token'))) {
            $this->addFlash('erreur', 'Jeton de sécurité invalide, demande non traitée.');

            return false;
        }

        if (!$demande->estEnAttente()) {
            $this->addFlash('erreur', 'Cette demande a déjà été traitée.');

            return false;
        }

        return true;
    }

    private function classer(DemandeCompteDepute $demande, string $etat): void
    {
        $demande->setEtat($etat);
        $demande->setTraiteeLe(new \DateTimeImmutable());
        $this->entityManager->flush();
    }

    private function envoyerLien(string $adresse, string $nom, string $lien): void
    {
        $courriel = (new Email())
            ->from(new Address('info@datan.fr', 'Datan'))
            ->to($adresse)
            ->subject('Lien d\'activation pour créer un compte Datan')
            ->text(sprintf(
                "Bonjour %s,\n\nVotre demande de compte Datan a été acceptée. Suivez ce lien pour créer votre compte et choisir votre mot de passe :\n%s\n\nL'équipe de Datan",
                $nom,
                $lien,
            ));

        $this->courrielleur->send($courriel);
    }
}
