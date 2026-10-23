<?php

namespace App\Controller\Admin;

use App\Entity\Parrainage;
use App\Entity\Utilisateur;
use App\Form\ParrainageMpType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Administration des parrainages (`Admin::parrainages` / `modify_parrainage`).
 *
 * La liste reprend celle du legacy : les parrainages émanant de députés en 2022,
 * avec le lien vers leur fiche quand il est résolu. Le seul écran d'édition
 * corrige le rattachement d'un parrainage à un député (`mpId`), à la main.
 *
 * Aucune règle d'administrateur ici : tout membre de la rédaction liste et
 * modifie (le legacy ne pose de garde ni sur `parrainages`, ni sur
 * `modify_parrainage`).
 */
#[Route('/admin/parrainages')]
#[IsGranted(Utilisateur::ROLE_REDACTEUR)]
class ParrainageController extends AbstractController
{
    /** Année de l'élection présidentielle des parrainages administrés. */
    private const ANNEE = 2022;

    /** Un parrainage émane d'un député quand son mandat est l'un de ceux-ci. */
    private const MANDATS_DEPUTE = ['député', 'députée'];

    public function __construct(
        private readonly Connection $connection,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'admin_parrainage_index', methods: ['GET'])]
    public function index(): Response
    {
        // Jointure à gauche : un parrainage dont le `mpId` n'est pas encore
        // renseigné (ou ne résout aucun député) doit figurer dans la liste — c'est
        // précisément lui qu'on vient corriger.
        $parrainages = $this->connection->fetchAllAssociative(
            'SELECT p.id, p.prenom, p.nom, p.mandat, p.circonscription, p.departement,
                    p.candidat, p.mp_id AS mpId, d.slug, d.dpt_slug AS dptSlug
             FROM parrainage p
             LEFT JOIN depute d ON d.mp_id = p.mp_id
             WHERE p.annee = :annee AND p.mandat IN (:mandats)
             ORDER BY p.nom ASC, p.prenom ASC',
            ['annee' => self::ANNEE, 'mandats' => self::MANDATS_DEPUTE],
            ['mandats' => \Doctrine\DBAL\ArrayParameterType::STRING],
        );

        return $this->render('admin/parrainage/index.html.twig', [
            'parrainages' => $parrainages,
        ]);
    }

    #[Route('/modify/{id}', name: 'admin_parrainage_modifier', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function modifier(Request $request, Parrainage $parrainage): Response
    {
        $formulaire = $this->createForm(ParrainageMpType::class, $parrainage);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('succes', 'Parrainage mis à jour.');

            return $this->redirectToRoute('admin_parrainage_index');
        }

        return $this->render('admin/parrainage/modifier.html.twig', [
            'formulaire' => $formulaire,
            'parrainage' => $parrainage,
        ]);
    }
}
