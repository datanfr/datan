<?php

namespace App\Controller;

use App\Entity\DemandeCompteDepute;
use App\Repository\DemandeCompteDeputeRepository;
use App\Repository\DeputeRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Demande d'ouverture d'un compte par un député, portée de `Users::demande_mp`.
 *
 * C'est la seule voie pour qu'un des 577 députés obtienne son accès sans passer
 * par la commande `app:utilisateur:creer`, lancée à la main.
 *
 * Mécanique de l'application d'origine : le député saisit son adresse
 * institutionnelle, on vérifie qu'elle figure bien parmi les contacts des
 * députés (`deputes_contacts.mailAn`, ici `depute.mail_an`) et qu'aucun compte
 * n'y est déjà rattaché, puis un jeton de 24 h est créé et un courriel
 * d'activation envoyé — la possession de l'adresse `@assemblee-nationale.fr`
 * valant preuve d'identité.
 *
 * Ce portage n'envoie pas de courriel (Mailjet relève du déploiement, comme
 * pour la newsletter) et `/register` — la création libre du compte — appartient
 * au chantier des comptes lecteurs. La demande devient donc une ligne en
 * attente que la rédaction relit et approuve (voir Admin\DemandeCompteController) :
 * le contrôle par l'adresse est reporté à l'envoi des identifiants, il n'est
 * pas perdu.
 *
 * Page **publique**, avec jeton CSRF : jamais de cache HTTP, le jeton est propre
 * à la session.
 */
class DemandeCompteController extends AbstractController
{
    #[Route('/demande-compte-depute', name: 'demande_compte_depute', methods: ['GET', 'POST'])]
    public function demande(
        Request $requete,
        DeputeRepository $deputes,
        DemandeCompteDeputeRepository $demandes,
        UtilisateurRepository $utilisateurs,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($requete->isMethod('POST')) {
            return $this->traiter($requete, $deputes, $demandes, $utilisateurs, $entityManager);
        }

        $reponse = $this->render('demande_compte/index.html.twig');

        // Aucun cache HTTP : le formulaire porte un jeton CSRF propre à la
        // session. `no-store` par-dessus le défaut privé, pour qu'aucun proxy
        // n'en garde une trace.
        $reponse->setPrivate();
        $reponse->headers->addCacheControlDirective('no-store', true);

        return $reponse;
    }

    private function traiter(
        Request $requete,
        DeputeRepository $deputes,
        DemandeCompteDeputeRepository $demandes,
        UtilisateurRepository $utilisateurs,
        EntityManagerInterface $entityManager,
    ): Response {
        if (!$this->isCsrfTokenValid('demande_compte_depute', (string) $requete->request->get('_token'))) {
            $this->addFlash('erreur', 'Votre session a expiré, merci de renvoyer votre demande.');

            return $this->redirectToRoute('demande_compte_depute');
        }

        $email = trim((string) $requete->request->get('email'));

        if ($email === '' || !filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('erreur', 'Merci de saisir une adresse électronique valide.');

            return $this->redirectToRoute('demande_compte_depute');
        }

        // L'adresse doit être celle d'un député dans notre base — le legacy la
        // cherche dans `deputes_contacts.mailAn`, notre colonne `depute.mail_an`.
        // La collation de MariaDB étant insensible à la casse, la comparaison ne
        // se soucie pas de la casse de saisie, comme le legacy.
        $depute = $deputes->findOneBy(['mailAn' => $email]);

        if ($depute === null) {
            $this->addFlash('erreur', "Nous ne vous trouvons pas dans notre base de données. N'hésitez pas à nous contacter : info@datan.fr.");

            return $this->redirectToRoute('demande_compte_depute');
        }

        // Un compte déjà rattaché à ce député : rien à demander, on renvoie à la
        // connexion (le lien permanent est au bas du formulaire).
        if ($utilisateurs->findOneBy(['depute' => $depute]) !== null) {
            $this->addFlash('erreur', 'Un compte Datan est déjà ouvert pour vous. Connectez-vous ci-dessous.');

            return $this->redirectToRoute('demande_compte_depute');
        }

        // Verrou anti-abus « une demande par député » : le legacy s'appuie sur un
        // captcha que ce portage n'a pas (affaire de déploiement, comme le
        // reste de la pile anti-spam). Tant qu'une demande n'est pas traitée, une
        // seconde saisie ne crée pas de doublon.
        if ($demandes->enAttentePourDepute($depute) !== null) {
            $this->addFlash('succes', 'Une demande est déjà en cours de traitement pour votre compte. La rédaction reviendra vers vous.');

            return $this->redirectToRoute('demande_compte_depute');
        }

        $demande = new DemandeCompteDepute();
        $demande->setDepute($depute);
        $demande->setEmail($email);

        $entityManager->persist($demande);
        $entityManager->flush();

        $this->addFlash('succes', "Votre demande a bien été transmise. La rédaction de Datan la traitera et vous fera parvenir vos identifiants à votre adresse de l'Assemblée nationale.");

        return $this->redirectToRoute('demande_compte_depute');
    }
}
