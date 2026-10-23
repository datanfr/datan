<?php

namespace App\Controller\Admin;

use App\Entity\ContactDepute;
use App\Entity\Depute;
use App\Entity\Utilisateur;
use App\Form\ContactDeputeType;
use App\Legislature;
use App\Repository\ContactDeputeRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Tableaux d'analyse « réseaux sociaux » de l'espace de rédaction
 * (`Admin::socialmedia`).
 *
 * Ce sont des vues en lecture seule qui aident la rédaction à repérer les
 * mouvements de députés (entrées, sorties, changements de groupe) pour tenir à
 * jour, à la main et hors de la base ouverte, les photos et les réseaux sociaux.
 * Le legacy ne pose ici aucune garde d'administrateur : tout membre de la
 * rédaction y accède.
 *
 * « Postes Assemblée » liste les mandats en organe de la législature en cours,
 * du plus récent au plus ancien (Deputes_model::get_postes_assemblee). Le legacy
 * les lit dans une table `mandat_secondaire` unique ; nous l'avons scindée, si
 * bien que l'écran réunit ses deux composantes de la 17e : les commissions
 * permanentes ({@see Commission}) et les délégations du Bureau ({@see Organe}).
 *
 * « Comptes X », de son côté, est alimenté : les réseaux sociaux des
 * députés sont récupérés dans `contact_depute` ({@see ContactDepute}) et cet
 * écran devient leur surface d'entretien — une fiche par député, éditable
 * ({@see editerDepute}), le legacy n'ayant, lui, aucun formulaire pour cela.
 */
#[Route('/admin/socialmedia')]
#[IsGranted(Utilisateur::ROLE_REDACTEUR)]
class SocialmediaController extends AbstractController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly EntityManagerInterface $em,
        private readonly ContactDeputeRepository $contacts,
    ) {
    }

    /**
     * Fiche d'édition des réseaux sociaux d'un député
     * (`socialmedia/editer/{mpId}`), le seul moyen d'entretenir cette donnée.
     *
     * Déclarée avant la route générique et plus prioritaire, comme l'historique,
     * pour que `editer/PAxxxx` ne soit pas avalé par le paramètre `{page}`.
     */
    #[Route('/editer/{mpId}', name: 'admin_socialmedia_editer', requirements: ['mpId' => '[A-Za-z0-9]+'], priority: 10, methods: ['GET', 'POST'])]
    public function editerDepute(string $mpId, Request $request): Response
    {
        $depute = $this->em->getRepository(Depute::class)->findOneBy(['mpId' => $mpId]);

        if ($depute === null) {
            throw $this->createNotFoundException('Député inconnu.');
        }

        // Une fiche neuve rattachée au député si aucune n'existe : un député
        // jamais renseigné doit pouvoir l'être ici.
        $contact = $this->contacts->pourDepute($depute);

        $formulaire = $this->createForm(ContactDeputeType::class, $contact);
        $formulaire->handleRequest($request);

        if ($formulaire->isSubmitted() && $formulaire->isValid()) {
            $contact->setMisAJourLe(new \DateTimeImmutable());
            $this->em->persist($contact);
            $this->em->flush();

            $this->addFlash('succes', 'Réseaux sociaux enregistrés.');

            return $this->redirectToRoute('admin_socialmedia', ['page' => 'x']);
        }

        return $this->render('admin/socialmedia/edition.html.twig', [
            'depute' => $depute,
            'contact' => $contact,
            'formulaire' => $formulaire,
        ]);
    }

    /**
     * Historique des groupes d'un député (`socialmedia/historique/{mpId}`).
     *
     * Déclaré avant la route générique, et plus prioritaire, pour que
     * `historique/PAxxxx` ne soit pas avalé par le paramètre `{page}`.
     */
    #[Route('/historique/{mpId}', name: 'admin_socialmedia_historique', requirements: ['mpId' => '[A-Za-z0-9]+'], priority: 10, methods: ['GET'])]
    public function historiqueDepute(string $mpId): Response
    {
        $depute = $this->connection->fetchAssociative(
            'SELECT firstname, lastname, mp_id AS mpId FROM depute WHERE mp_id = :mpId',
            ['mpId' => $mpId],
        );

        if ($depute === false) {
            throw $this->createNotFoundException('Député inconnu.');
        }

        // Historique des rattachements de groupe depuis la 15e législature, comme
        // le legacy (`mandat_groupe` → notre `fonction_groupe`, `organes` → `groupe`).
        $historique = $this->connection->fetchAllAssociative(
            'SELECT g.libelle AS groupe, fg.code_qualite AS codeQualite,
                    fg.date_debut AS dateDebut, fg.date_fin AS dateFin
             FROM fonction_groupe fg
             JOIN groupe g ON g.id = fg.groupe_id
             WHERE fg.depute_id = (SELECT id FROM depute WHERE mp_id = :mpId)
               AND g.legislature >= 15
             ORDER BY fg.date_debut DESC',
            ['mpId' => $mpId],
        );

        return $this->render('admin/socialmedia/historique.html.twig', [
            'depute' => $depute,
            'historique' => $historique,
        ]);
    }

    #[Route('/{page}', name: 'admin_socialmedia', requirements: ['page' => 'deputes_entrants|deputes_sortants|postes_assemblee|groupes_entrants|historique|x'], methods: ['GET'])]
    public function page(string $page): Response
    {
        return match ($page) {
            'deputes_entrants' => $this->tableau('Députés entrants', $this->deputesEntrants(), [
                'prenom' => 'Prénom', 'nom' => 'Nom', 'mpId' => 'mpId',
                'dateDebut' => 'Prise de fonction', 'dateFin' => 'Fin',
            ]),
            'deputes_sortants' => $this->tableau('Députés sortants', $this->deputesSortants(), [
                'prenom' => 'Prénom', 'nom' => 'Nom', 'mpId' => 'mpId',
                'dateDebut' => 'Début', 'dateFin' => 'Fin',
            ]),
            'groupes_entrants' => $this->tableau('Nouveaux membres de groupe', $this->groupesEntrants(), [
                'prenom' => 'Prénom', 'nom' => 'Nom', 'mpId' => 'mpId',
                'groupe' => 'Groupe', 'codeQualite' => 'Qualité',
                'dateDebut' => 'Début', 'dateFin' => 'Fin',
            ]),
            'historique' => $this->historiqueListe(),
            'x' => $this->render('admin/socialmedia/comptes_x.html.twig', [
                'titre' => 'Comptes X des députés',
                'lignes' => $this->comptesX(),
            ]),
            'postes_assemblee' => $this->tableau('Nouveaux postes Assemblée', $this->postesAssemblee(), [
                'prenom' => 'Prénom', 'nom' => 'Nom', 'mpId' => 'mpId',
                'dateDebut' => 'Prise de fonction', 'dateFin' => 'Fin',
                'codeQualite' => 'Qualité', 'libelle' => 'Organe',
            ]),
            default => throw $this->createNotFoundException(),
        };
    }

    /**
     * Députés de la législature en cours et leurs réseaux sociaux, pour la table
     * « Comptes X » — enrichie des autres comptes et d'un lien d'édition, là où
     * le legacy n'affichait que le pseudo X et un lien vers x.com.
     *
     * @return list<array<string, mixed>>
     */
    private function comptesX(): array
    {
        // Le rattachement à la législature passe par un `IN` sur les mandats
        // plutôt qu'une jointure, pour ne pas dédoubler un député aux mandats
        // interrompus. La fiche de contact est 1:1, jointe à gauche : un député
        // jamais renseigné apparaît quand même, avec des comptes vides.
        return $this->connection->fetchAllAssociative(
            'SELECT d.firstname AS prenom, d.lastname AS nom, d.mp_id AS mpId,
                    c.twitter, c.bluesky, c.facebook, c.site_web AS siteWeb
             FROM depute d
             LEFT JOIN contact_depute c ON c.depute_id = d.id
             WHERE d.id IN (SELECT depute_id FROM mandat WHERE legislature = :leg)
             ORDER BY d.lastname ASC, d.firstname ASC',
            ['leg' => Legislature::COURANTE],
        );
    }

    /**
     * Mandats en organe de la législature en cours, du plus récent au plus
     * ancien — l'écran « Postes Assemblée ».
     *
     * Le legacy (`Deputes_model::get_postes_assemblee`) lit une table
     * `mandat_secondaire` unique, où il ne retient que trois types d'organe
     * (`daily.php:473`) : COMPER, DELEGBUREAU et PARPOL. Nous l'avons scindée —
     * COMPER dans `fonction_commission`, DELEGBUREAU dans `mandat_organe`, PARPOL
     * sur `depute.parti_id`. On réunit donc ici les deux composantes qui portent
     * une législature ; PARPOL n'en porte pas et n'a jamais paru sur cet écran.
     * Aucun filtre de qualité, comme le legacy (toutes qualités confondues).
     *
     * La moitié COMPER compte plus de lignes que datan.fr (~9 900 contre ~8 800) :
     * c'est la donnée fraîche de `fonction_commission`, la même divergence déjà
     * actée pour les commissions (l'open data ajoute des mandats au fil du temps,
     * la copie de production est un instantané plus ancien). Notre chiffre est le
     * plus à jour.
     *
     * @return list<array<string, mixed>>
     */
    private function postesAssemblee(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT d.firstname AS prenom, d.lastname AS nom, d.mp_id AS mpId,
                    fc.date_debut AS dateDebut, fc.date_fin AS dateFin,
                    fc.code_qualite AS codeQualite, co.libelle AS libelle
             FROM fonction_commission fc
             JOIN depute d ON d.id = fc.depute_id
             JOIN commission co ON co.id = fc.commission_id
             WHERE fc.legislature = ?
             UNION ALL
             SELECT d.firstname, d.lastname, d.mp_id,
                    mo.date_debut, mo.date_fin, mo.code_qualite, o.libelle
             FROM mandat_organe mo
             JOIN depute d ON d.id = mo.depute_id
             JOIN organe o ON o.id = mo.organe_id
             WHERE mo.legislature = ?
             ORDER BY dateDebut DESC',
            [Legislature::COURANTE, Legislature::COURANTE],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function deputesEntrants(): array
    {
        // Notre table `mandat` est le mandat parlementaire principal (le
        // « membre » du legacy) : pas de filtre `codeQualite` à poser.
        return $this->connection->fetchAllAssociative(
            'SELECT d.firstname AS prenom, d.lastname AS nom, d.mp_id AS mpId,
                    m.date_debut AS dateDebut, m.date_fin AS dateFin
             FROM mandat m
             JOIN depute d ON d.id = m.depute_id
             WHERE m.legislature = :leg
             ORDER BY m.date_debut DESC',
            ['leg' => Legislature::COURANTE],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function deputesSortants(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT d.firstname AS prenom, d.lastname AS nom, d.mp_id AS mpId,
                    m.date_debut AS dateDebut, m.date_fin AS dateFin
             FROM mandat m
             JOIN depute d ON d.id = m.depute_id
             WHERE m.legislature = :leg AND m.date_fin IS NOT NULL
             ORDER BY m.date_fin DESC',
            ['leg' => Legislature::COURANTE],
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function groupesEntrants(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT d.firstname AS prenom, d.lastname AS nom, d.mp_id AS mpId,
                    g.libelle AS groupe, fg.code_qualite AS codeQualite,
                    fg.date_debut AS dateDebut, fg.date_fin AS dateFin
             FROM fonction_groupe fg
             JOIN depute d ON d.id = fg.depute_id
             JOIN groupe g ON g.id = fg.groupe_id
             WHERE g.legislature = :leg
             ORDER BY fg.date_debut DESC',
            ['leg' => Legislature::COURANTE],
        );
    }

    private function historiqueListe(): Response
    {
        // Les députés ayant exercé un mandat sous la législature en cours, chacun
        // menant à son historique de groupes (comme la liste du legacy).
        $deputes = $this->connection->fetchAllAssociative(
            'SELECT DISTINCT d.firstname AS prenom, d.lastname AS nom, d.mp_id AS mpId
             FROM depute d
             JOIN mandat m ON m.depute_id = d.id AND m.legislature = :leg
             ORDER BY d.lastname ASC, d.firstname ASC',
            ['leg' => Legislature::COURANTE],
        );

        return $this->render('admin/socialmedia/liste.html.twig', [
            'titre' => 'Historique des groupes par député',
            'deputes' => $deputes,
        ]);
    }

    /**
     * @param list<array<string, mixed>> $lignes
     * @param array<string, string>      $colonnes
     */
    private function tableau(string $titre, array $lignes, array $colonnes): Response
    {
        return $this->render('admin/socialmedia/table.html.twig', [
            'titre' => $titre,
            'colonnes' => $colonnes,
            'lignes' => $lignes,
        ]);
    }

    private function indisponible(string $titre, string $message): Response
    {
        return $this->render('admin/socialmedia/indisponible.html.twig', [
            'titre' => $titre,
            'message' => $message,
        ]);
    }
}
