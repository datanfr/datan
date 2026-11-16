<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class ConnexionController extends AbstractController
{
    /**
     * La page de connexion, servie à `/login`.
     *
     * L'application d'origine ouvre `/login` à tout le monde — lecteurs comme
     * équipes — et c'est cette adresse que les liens du site attendent (le
     * « Connexion » du pied de page pointe dessus). Le pare-feu l'utilise comme
     * `login_path` et `check_path` : le formulaire y poste, un seul pour les
     * trois familles de comptes.
     */
    #[Route('/login', name: 'connexion', methods: ['GET', 'POST'])]
    public function connexion(AuthenticationUtils $authentification): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('apres_connexion');
        }

        $reponse = $this->render('connexion/index.html.twig', [
            'dernier_identifiant' => $authentification->getLastUsername(),
            'erreur' => $authentification->getLastAuthenticationError(),
        ]);

        // Formulaire à jeton CSRF propre à la session : jamais de cache, comme
        // les autres pages de session (/demande-compte-depute). La session pose
        // déjà `private` ; `no-store` interdit en plus toute copie locale.
        $reponse->setPrivate();
        $reponse->headers->addCacheControlDirective('no-store', true);

        return $reponse;
    }

    /**
     * `/connexion` était l'adresse de la connexion pendant le portage, avant que
     * `/login` — l'adresse du legacy — ne soit rétablie. On la redirige en 301
     * plutôt que de la laisser tomber en 404 : un signet de la rédaction ne doit
     * pas se casser (CLAUDE.md, « une adresse ne change pas »).
     */
    #[Route('/connexion', name: 'connexion_ancienne', methods: ['GET'])]
    public function connexionAncienne(): Response
    {
        return $this->redirectToRoute('connexion', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Aiguillage vers l'espace du compte connecté.
     *
     * Les trois familles n'atterrissent pas au même endroit, et rien ne le dit
     * avant l'authentification : un député dans son tableau de bord, un
     * rédacteur sur ses décryptages, un lecteur sur l'accueil (le `redirect()`
     * du legacy renvoyait le lecteur à la page d'accueil). Envoyer un lecteur
     * sur les décryptages lui vaudrait un 403.
     */
    #[Route('/apres-connexion', name: 'apres_connexion', methods: ['GET'])]
    public function apresConnexion(): Response
    {
        return $this->redirectToRoute(match (true) {
            $this->isGranted(Utilisateur::ROLE_DEPUTE) => 'dashboard',
            $this->isGranted(Utilisateur::ROLE_REDACTEUR) => 'admin_decryptage_index',
            default => 'home',
        });
    }

    /**
     * Interceptée par le pare-feu ; cette méthode n'est jamais exécutée.
     */
    #[Route('/deconnexion', name: 'deconnexion', methods: ['GET'])]
    public function deconnexion(): never
    {
        throw new \LogicException('Déconnexion prise en charge par le pare-feu de sécurité.');
    }
}
