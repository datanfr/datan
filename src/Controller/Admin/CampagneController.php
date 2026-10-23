<?php

namespace App\Controller\Admin;

use App\Entity\Campagne;
use App\Entity\Utilisateur;
use App\Form\CampagneType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Administration des campagnes de dons (`Admin::campaigns_list` et suivantes de
 * l'application d'origine).
 *
 * Ce CRUD rend l'encart « Faire un don » pilotable : la page publique interroge
 * `/campaign/current_active_campaigns` ({@see \App\Controller\CampagneController})
 * et n'affiche que les campagnes activées et dans leur fenêtre de dates.
 *
 * Règles de rôle reprises du legacy : tout membre de la rédaction crée, modifie
 * et active/désactive une campagne ; **seul un administrateur peut en
 * supprimer** (`Admin::delete_campaign` renvoie hors de l'espace si
 * `usernameType != admin`).
 */
#[Route('/admin/campagnes')]
#[IsGranted(Utilisateur::ROLE_REDACTEUR)]
class CampagneController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'admin_campagne_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/campagne/index.html.twig', [
            'campagnes' => $this->entityManager->getRepository(Campagne::class)->findBy([], ['id' => 'ASC']),
            'positions' => array_flip(CampagneType::POSITIONS),
        ]);
    }

    #[Route('/create', name: 'admin_campagne_creer', methods: ['GET', 'POST'])]
    public function creer(Request $request): Response
    {
        $campagne = new Campagne();

        $formulaire = $this->createForm(CampagneType::class, $campagne);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            // Le legacy garde l'identifiant de l'auteur ; notre colonne `auteur`
            // est un libellé, on y inscrit donc le nom affiché du rédacteur.
            $campagne->setAuteur($this->nomUtilisateur());
            $campagne->setCreeLe(new \DateTimeImmutable());
            $campagne->setModifieLe(new \DateTimeImmutable());

            $this->entityManager->persist($campagne);
            $this->entityManager->flush();

            $this->addFlash('succes', 'Campagne créée. Activez-la depuis la liste pour la rendre visible.');

            return $this->redirectToRoute('admin_campagne_index');
        }

        return $this->render('admin/campagne/form.html.twig', [
            'formulaire' => $formulaire,
            'campagne' => null,
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_campagne_modifier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function modifier(Request $request, Campagne $campagne): Response
    {
        $formulaire = $this->createForm(CampagneType::class, $campagne);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $campagne->setModifieLe(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->addFlash('succes', 'Campagne enregistrée.');

            return $this->redirectToRoute('admin_campagne_index');
        }

        return $this->render('admin/campagne/form.html.twig', [
            'formulaire' => $formulaire,
            'campagne' => $campagne,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_campagne_supprimer', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    #[IsGranted(Utilisateur::ROLE_ADMIN)]
    public function supprimer(Request $request, Campagne $campagne): Response
    {
        if ($request->isMethod('POST')) {
            if (!$this->isCsrfTokenValid('supprimer_campagne_' . $campagne->getId(), (string) $request->request->get('_token'))) {
                $this->addFlash('erreur', 'Jeton de sécurité invalide, suppression annulée.');

                return $this->redirectToRoute('admin_campagne_index');
            }

            $this->entityManager->remove($campagne);
            $this->entityManager->flush();

            $this->addFlash('succes', 'Campagne supprimée.');

            return $this->redirectToRoute('admin_campagne_index');
        }

        return $this->render('admin/campagne/supprimer.html.twig', ['campagne' => $campagne]);
    }

    /**
     * Bascule l'activation d'une campagne depuis la liste.
     *
     * Reprend `Admin::toggle_campaign_active` : la valeur postée est ramenée à un
     * entier 0/1 avant d'être écrite. La colonne `active` est un SMALLINT, jamais
     * un booléen — le pilote mysqli lierait un `false` en chaîne vide, que
     * MariaDB refuse dans une colonne entière.
     */
    #[Route('/toggle', name: 'admin_campagne_basculer', methods: ['POST'])]
    public function basculer(Request $request): Response
    {
        $id = $request->request->get('id');

        if (!$this->isCsrfTokenValid('basculer_campagne', (string) $request->request->get('_token'))) {
            $this->addFlash('erreur', 'Jeton de sécurité invalide, activation annulée.');

            return $this->redirectToRoute('admin_campagne_index');
        }

        $campagne = $id !== null
            ? $this->entityManager->getRepository(Campagne::class)->find((int) $id)
            : null;

        if ($campagne === null) {
            $this->addFlash('erreur', 'Campagne introuvable.');

            return $this->redirectToRoute('admin_campagne_index');
        }

        $campagne->setActive($request->request->getBoolean('is_active') ? 1 : 0);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_campagne_index');
    }

    private function nomUtilisateur(): ?string
    {
        $utilisateur = $this->getUser();

        return $utilisateur instanceof Utilisateur ? $utilisateur->getNom() : null;
    }
}
