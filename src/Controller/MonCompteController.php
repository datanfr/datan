<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * L'espace « mon compte », porté de `Users::compte`, `modify_personal_data`,
 * `modify_password` et `delete_account`.
 *
 * Accessible aux **trois** familles de comptes (lecteur, rédaction, député) : le
 * pare-feu exige seulement d'être connecté (`^/mon-compte` → IS_AUTHENTICATED).
 * C'est par cette page qu'un député change enfin le mot de passe provisoire
 * qu'on lui a transmis.
 *
 * Pages authentifiées, jamais mises en cache : le contrôleur lit `getUser()` et
 * la coque publique (barre de navigation) est celle du site, comme le legacy qui
 * charge ici `templates/header` (navbar) et non `header_no_navbar`.
 */
#[Route('/mon-compte')]
class MonCompteController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UtilisateurRepository $utilisateurs,
        private readonly UserPasswordHasherInterface $hacheur,
    ) {
    }

    #[Route('', name: 'mon_compte', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('mon_compte/index.html.twig');
    }

    #[Route('/modifier-donnees-personnelles', name: 'mon_compte_donnees', methods: ['GET', 'POST'])]
    public function donnees(Request $requete): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        if ($requete->isMethod('GET')) {
            return $this->render('mon_compte/donnees.html.twig');
        }

        if (!$this->isCsrfTokenValid('mon_compte_donnees', (string) $requete->request->get('_token'))) {
            $this->addFlash('erreur', 'Votre session a expiré, merci de recommencer.');

            return $this->redirectToRoute('mon_compte_donnees');
        }

        $email = trim((string) $requete->request->get('email'));
        $pseudo = trim((string) $requete->request->get('pseudo'));
        $nom = trim((string) $requete->request->get('name'));
        $codePostal = trim((string) $requete->request->get('zipcode'));

        $erreurs = [];

        if ($nom === '') {
            $erreurs[] = 'Le nom est obligatoire.';
        }

        if ($email === '' || !filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $erreurs[] = 'Merci de saisir une adresse électronique valide.';
        } elseif ($this->prisParUnAutre('email', $email, $utilisateur)) {
            $erreurs[] = 'Cette adresse électronique est déjà utilisée.';
        }

        // `alpha_dash` du legacy : lettres, chiffres, tiret et souligné — les
        // identifiants de députés (« julien-guibert ») portent un tiret, d'où ce
        // jeu plus large qu'à l'inscription d'un lecteur.
        if ($pseudo === '' || preg_match('/^[A-Za-z0-9_-]+$/', $pseudo) !== 1) {
            $erreurs[] = 'Le pseudo ne peut contenir que des lettres, chiffres, tirets et soulignés.';
        } elseif ($this->prisParUnAutre('identifiant', $pseudo, $utilisateur)) {
            $erreurs[] = 'Ce pseudo est déjà pris.';
        }

        // Le code postal n'a de sens que pour un lecteur ; on ne l'exige que de
        // lui. Un député ou un rédacteur n'en renseigne pas.
        if ($utilisateur->estLecteur() && ($codePostal === '' || !ctype_digit($codePostal))) {
            $erreurs[] = 'Le code postal doit être un nombre.';
        }

        if ($erreurs !== []) {
            foreach ($erreurs as $erreur) {
                $this->addFlash('erreur', $erreur);
            }

            return $this->redirectToRoute('mon_compte_donnees');
        }

        $utilisateur->setEmail($email);
        $utilisateur->setIdentifiant($pseudo);
        $utilisateur->setNom($nom);
        if ($utilisateur->estLecteur()) {
            $utilisateur->setCodePostal($codePostal);
        }
        $this->entityManager->flush();

        $this->addFlash('succes', 'Vos données personnelles ont été changées.');

        return $this->redirectToRoute('mon_compte');
    }

    #[Route('/modifier-password', name: 'mon_compte_mot_de_passe', methods: ['GET', 'POST'])]
    public function motDePasse(Request $requete): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        if ($requete->isMethod('GET')) {
            return $this->render('mon_compte/mot_de_passe.html.twig');
        }

        if (!$this->isCsrfTokenValid('mon_compte_mot_de_passe', (string) $requete->request->get('_token'))) {
            $this->addFlash('erreur', 'Votre session a expiré, merci de recommencer.');

            return $this->redirectToRoute('mon_compte_mot_de_passe');
        }

        $actuel = (string) $requete->request->get('current');
        $nouveau = (string) $requete->request->get('new');
        $confirmation = (string) $requete->request->get('new_confirmation');

        // Le legacy vérifie le mot de passe actuel avant de le changer : sans
        // cela, un poste laissé ouvert suffit à voler le compte.
        if (!$this->hacheur->isPasswordValid($utilisateur, $actuel)) {
            $this->addFlash('erreur', "Votre mot de passe actuel n'est pas le bon.");

            return $this->redirectToRoute('mon_compte_mot_de_passe');
        }

        if (mb_strlen($nouveau) < 8) {
            $this->addFlash('erreur', 'Le nouveau mot de passe doit faire au moins 8 caractères.');

            return $this->redirectToRoute('mon_compte_mot_de_passe');
        }

        if ($nouveau !== $confirmation) {
            $this->addFlash('erreur', 'Les deux mots de passe ne correspondent pas.');

            return $this->redirectToRoute('mon_compte_mot_de_passe');
        }

        $utilisateur->setPassword($this->hacheur->hashPassword($utilisateur, $nouveau));
        $this->entityManager->flush();

        $this->addFlash('succes', 'Le mot de passe a été changé.');

        return $this->redirectToRoute('mon_compte');
    }

    #[Route('/supprimer-compte', name: 'mon_compte_supprimer', methods: ['GET'])]
    public function supprimer(): Response
    {
        return $this->render('mon_compte/supprimer.html.twig');
    }

    #[Route('/supprimer-compte/confirmed', name: 'mon_compte_supprimer_confirme', methods: ['POST'])]
    public function supprimerConfirme(Request $requete): Response
    {
        /** @var Utilisateur $utilisateur */
        $utilisateur = $this->getUser();

        // Le legacy supprimait sur un simple lien (GET) : une image piégée
        // suffisait à effacer le compte. On exige un POST porteur d'un jeton CSRF.
        if (!$this->isCsrfTokenValid('supprimer_compte', (string) $requete->request->get('_token'))) {
            $this->addFlash('erreur', 'Votre session a expiré, merci de recommencer.');

            return $this->redirectToRoute('mon_compte');
        }

        // Les décryptages d'un rédacteur survivent, leur auteur passant à NULL
        // (Decryptage.auteur, onDelete SET NULL) ; les explications d'un député
        // pendent au député, pas au compte. La suppression n'emporte donc que le
        // compte de connexion.
        $this->entityManager->remove($utilisateur);
        $this->entityManager->flush();

        // Le pare-feu vide la session sur la route de déconnexion, comme le
        // `redirect('logout')` du legacy.
        return $this->redirectToRoute('deconnexion');
    }

    /**
     * L'identifiant ou l'e-mail est-il déjà porté par un **autre** compte ? Le
     * legacy ne revérifiait pas l'unicité à la modification (un doublon de pseudo
     * cassait la connexion) — défaut corrigé, en s'excluant soi-même.
     */
    private function prisParUnAutre(string $champ, string $valeur, Utilisateur $soi): bool
    {
        $existant = $this->utilisateurs->findOneBy([$champ => $valeur]);

        return $existant !== null && $existant->getId() !== $soi->getId();
    }
}
