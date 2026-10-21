<?php

namespace App\Controller;

use App\CouleurGroupe;
use App\Enum\PopulationGroupes;
use App\Http\RechauffeurCache;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Liste des groupes parlementaires (Groupes::index de l'application CodeIgniter
 * d'origine, vue application/views/groupes/all.php).
 */
class GroupeListController extends AbstractController
{
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /** Les non-inscrits ne forment pas un groupe : ils sont comptés à part, jamais listés. */
    private const NON_INSCRITS = 'NI';

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/groupes', name: 'groupes_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->liste(Legislature::COURANTE, PopulationGroupes::Actifs);
    }

    #[Route('/groupes/inactifs', name: 'groupes_inactifs', methods: ['GET'])]
    public function inactifs(): Response
    {
        return $this->liste(Legislature::COURANTE, PopulationGroupes::Dissous);
    }

    #[Route('/groupes/legislature-{legislature}', name: 'groupes_legislature', requirements: ['legislature' => '\d+'], methods: ['GET'])]
    public function legislature(int $legislature): Response
    {
        if ($legislature === Legislature::COURANTE) {
            return $this->redirectToRoute('groupes_index', [], Response::HTTP_MOVED_PERMANENTLY);
        }

        if ($legislature < Legislature::PREMIERE) {
            throw $this->createNotFoundException('Législature inconnue.');
        }

        return $this->liste($legislature, PopulationGroupes::Tous);
    }

    private function liste(int $legislature, PopulationGroupes $population): Response
    {
        $actifs = $population === PopulationGroupes::Actifs;
        $rattachement = $actifs ? $this->rattachement($legislature) : ['en_groupe' => 0, 'non_inscrits' => 0];

        $response = $this->render('groupe/all.html.twig', [
            'legislature' => $legislature,
            'legislature_courante' => Legislature::COURANTE,
            'legislatures' => Legislature::publiees(),
            'actifs' => $actifs,
            'dissous' => $population === PopulationGroupes::Dissous,
            'groupes' => $this->groupes($legislature, $population),
            // Le nombre de groupes encore en activité, pour renvoyer vers eux
            // depuis la page des groupes dissous.
            'nombre_actifs' => $population === PopulationGroupes::Dissous ? $this->nombreActifs($legislature) : 0,
            'nombre_en_groupe' => $rattachement['en_groupe'],
            'nombre_non_inscrits' => $rattachement['non_inscrits'],
            'fil_ariane' => $this->filAriane($legislature, $population),
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Fil d'Ariane des listes, à l'identique du contrôleur Groupes de
     * l'origine — « Législature N » ici, quand les députés disent
     * « Nème législature ».
     *
     * @return list<array{nom: string, url: string}>
     */
    private function filAriane(int $legislature, PopulationGroupes $population): array
    {
        $fil = [
            ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
            ['nom' => 'Groupes', 'url' => $this->generateUrl('groupes_index')],
        ];

        if ($population === PopulationGroupes::Dissous) {
            $fil[] = ['nom' => 'Anciens groupes', 'url' => $this->generateUrl('groupes_inactifs')];
        } elseif ($legislature !== Legislature::COURANTE) {
            $fil[] = ['nom' => 'Législature ' . $legislature, 'url' => $this->generateUrl('groupes_legislature', ['legislature' => $legislature])];
        }

        return $fil;
    }

    /**
     * Groupes d'une législature, avec leur effectif.
     *
     * L'effectif se compte sur `depute.groupe_id`, qui porte le rattachement
     * courant : il n'est renseigné que pour la législature en cours, d'où le
     * classement par effectif ici et par libellé sur les législatures passées —
     * exactement comme dans l'application d'origine.
     *
     * @return list<array<string, mixed>>
     */
    private function groupes(int $legislature, PopulationGroupes $population): array
    {
        $filtre = match ($population) {
            PopulationGroupes::Actifs => ' AND g.date_fin IS NULL',
            PopulationGroupes::Dissous => ' AND g.date_fin IS NOT NULL',
            PopulationGroupes::Tous => '',
        };

        // Une législature achevée n'a plus d'effectif à afficher : ses groupes
        // sont alors rangés par libellé, comme dans l'application d'origine.
        $tri = $population === PopulationGroupes::Tous ? 'g.libelle' : 'effectif DESC, g.libelle';

        return $this->connection->fetchAllAssociative(
            'SELECT g.libelle, g.libelle_abrev, g.legislature,
                    g.date_debut, g.date_fin, COUNT(d.id) AS effectif,
                    ' . CouleurGroupe::SQL . ' AS couleur
             FROM groupe g
             LEFT JOIN depute d ON d.groupe_id = g.id
             WHERE g.legislature = :legislature AND g.libelle_abrev <> :ni' . $filtre . '
             GROUP BY g.id
             ORDER BY ' . $tri,
            ['legislature' => $legislature, 'ni' => self::NON_INSCRITS],
        );
    }

    /** Nombre de groupes encore en activité sous une législature. */
    private function nombreActifs(int $legislature): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM groupe g
             WHERE g.legislature = :legislature AND g.libelle_abrev <> :ni AND g.date_fin IS NULL',
            ['legislature' => $legislature, 'ni' => self::NON_INSCRITS],
        );
    }

    /**
     * Nombre de députés rattachés à un groupe et nombre de non-inscrits.
     *
     * @return array{en_groupe: int, non_inscrits: int}
     */
    private function rattachement(int $legislature): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT SUM(g.libelle_abrev <> :ni) AS en_groupe,
                    SUM(g.libelle_abrev = :ni) AS non_inscrits
             FROM depute d
             JOIN groupe g ON g.id = d.groupe_id
             WHERE g.legislature = :legislature',
            ['legislature' => $legislature, 'ni' => self::NON_INSCRITS],
        ) ?: [];

        return [
            'en_groupe' => (int) ($row['en_groupe'] ?? 0),
            'non_inscrits' => (int) ($row['non_inscrits'] ?? 0),
        ];
    }
}
