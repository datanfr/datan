<?php

namespace App\Controller;

use App\CouleurGroupe;
use App\Groupe\SoutienGouvernement;
use App\Http\RechauffeurCache;
use App\Legislature;
use App\Twig\DatanExtension;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Page d'accueil (Home::index de l'application CodeIgniter d'origine,
 * vue application/views/home/index.php).
 */
class HomeController extends AbstractController
{
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    private const NON_INSCRITS = 'NI';

    /**
     * Blocs politiques de l'hémicycle, repris tels quels de
     * Groupes_model::get_blocs().
     */
    private const BLOCS = [
        'gauche' => ['LFI-NFP', 'SOC', 'ECOS', 'GDR'],
        'centre' => ['EPR', 'DEM', 'HOR'],
        'droite' => ['DR'],
        'extreme_droite' => ['RN', 'UDR', 'UDDPLR'],
    ];

    /** Mois abrégés du site d'origine (months_abbrev()). */
    private const MOIS_ABREGES = [
        1 => 'janv.', 'févr.', 'mars', 'avr.', 'mai', 'juin',
        'juill.', 'août', 'sept.', 'oct.', 'nov.', 'déc.',
    ];

    /**
     * Ordre des groupes dans l'hémicycle, de la gauche à la droite de
     * l'hémicycle (Groupes_model::get_groupes_sorted()).
     */
    private const ORDRE_HEMICYCLE = [
        'GDR', 'LFI-NFP', 'SOC', 'ECOS', 'EPR', 'DEM', 'HOR', 'LIOT', 'DR', 'UDDPLR', 'RN', 'NI',
    ];

    /** Un député en exercice : un mandat encore ouvert, `%s` étant l'alias de `depute`. */
    private const EN_EXERCICE = 'EXISTS (SELECT 1 FROM mandat m
                                         WHERE m.depute_id = %s.id
                                           AND m.legislature = :legislature
                                           AND m.date_fin IS NULL)';

    private const MUNICIPALES = 'municipales-2026';

    public function __construct(
        private readonly Connection $connection,
        private readonly SoutienGouvernement $soutienGouvernement,
    ) {
    }

    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(): Response
    {
        $groupes = $this->groupesHemicycle(Legislature::COURANTE);

        // « Quels groupes soutiennent le gouvernement ? » : même décompte que le
        // comparatif de la fiche de groupe. Le groupe d'opposition le plus
        // proche du gouvernement est le premier de la liste hors majorité.
        $soutienGroupes = $this->soutienGouvernement->tousLesGroupes(Legislature::COURANTE);
        $soutienOpposition = null;
        foreach ($soutienGroupes as $ligne) {
            if (!\in_array($ligne['libelle_abrev'], SoutienGouvernement::MAJORITE, true)) {
                $soutienOpposition = $ligne;
                break;
            }
        }

        $response = $this->render('home/index.html.twig', [
            'legislature' => Legislature::COURANTE,
            'groupes' => $groupes,
            // Les non-inscrits ne comptent pas comme un groupe politique.
            'nombre_groupes' => count(array_filter($groupes, static fn (array $g) => $g['libelle_abrev'] !== self::NON_INSCRITS)),
            'blocs' => $this->blocs($groupes),
            // L'hémicycle est dessiné par Chart.js (donut + bulles d'effectif),
            // comme sur datan.fr — les groupes y vont de la gauche à la droite
            // de l'hémicycle.
            'groupes_hemicycle' => $this->ordreHemicycle($groupes),
            'effectif_total' => array_sum(array_column($groupes, 'effectif')),
            'decryptages' => $this->derniersDecryptages(),
            'election_municipales' => $this->electionMunicipales(),
            'explications' => $this->dernieresExplications(),
            'placeholder' => $this->placeholderRecherche(),
            'posts' => $this->derniersArticles(),
            'soutien_groupes' => $soutienGroupes,
            'soutien_opposition' => $soutienOpposition,
            'majorite' => SoutienGouvernement::MAJORITE,
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Exemple glissé dans le champ de recherche : un député en exercice ou un
     * groupe courant, à pile ou face — le tirage de l'application d'origine
     * (`rand(0, 10) < 5`), repris tel quel. Le cache HTTP fige l'exemple une
     * heure ; l'origine, derrière son propre cache, vit la même chose.
     */
    private function placeholderRecherche(): string
    {
        if (mt_rand(0, 10) < 5) {
            $depute = $this->connection->fetchAssociative(
                'SELECT d.firstname, d.lastname
                 FROM depute d
                 WHERE d.dpt_slug IS NOT NULL
                   AND EXISTS (SELECT 1 FROM mandat m
                               WHERE m.depute_id = d.id
                                 AND m.legislature = :legislature
                                 AND m.date_fin IS NULL)
                 ORDER BY RAND() LIMIT 1',
                ['legislature' => Legislature::COURANTE],
            );

            if ($depute !== false) {
                return $depute['firstname'] . ' ' . $depute['lastname'];
            }
        }

        // Les non-inscrits ne sont pas un groupe politique : le legacy les
        // écarte du tirage (`libelle != 'Non inscrit'`, Groupes_model:191).
        $groupe = $this->connection->fetchAssociative(
            'SELECT libelle, libelle_abrev FROM groupe
             WHERE legislature = :legislature AND date_fin IS NULL
               AND libelle_abrev != :nonInscrits
             ORDER BY RAND() LIMIT 1',
            ['legislature' => Legislature::COURANTE, 'nonInscrits' => self::NON_INSCRITS],
        );

        return $groupe === false ? 'Assemblée nationale' : $groupe['libelle'] . ' (' . $groupe['libelle_abrev'] . ')';
    }

    /**
     * Bloc « Élections municipales 2026 » : compteurs et ventilation par groupe
     * (Home::index du legacy, via Elections_model::count_candidats() et
     * get_n_candidates_all_groups() sur la vue `candidate_full`).
     *
     * Le legacy ne compte que les candidats dont le député siège encore
     * (`candidate_full.active`) : même critère ici par l'existence d'un mandat
     * ouvert. L'effectif du groupe suit la même règle, comme `groupes_effectif`.
     * Le tri s'arrête à la part décroissante, sans départage des égalités —
     * c'est celui du legacy.
     *
     * La donnée vient d'`app:import:elections`, pas de la synchronisation
     * quotidienne : sans import joué, le bloc disparaît au lieu d'afficher zéro.
     *
     * @return array{candidats: int, tetes: int, groupes: list<array<string, mixed>>}|null
     */
    private function electionMunicipales(): ?array
    {
        $actif = sprintf(self::EN_EXERCICE, 'd');
        $effectif = '(SELECT COUNT(*) FROM depute d2 WHERE d2.groupe_id = g.id AND ' . sprintf(self::EN_EXERCICE, 'd2') . ')';

        $compteurs = $this->connection->fetchAssociative(
            "SELECT COUNT(*) AS candidats, COALESCE(SUM(c.position = 'Tête de liste'), 0) AS tetes
             FROM candidature c
             JOIN election e ON e.id = c.election_id
             JOIN depute d ON d.id = c.depute_id
             WHERE e.slug = :slug AND c.visible = 1 AND c.candidat = 1 AND $actif",
            ['slug' => self::MUNICIPALES, 'legislature' => Legislature::COURANTE],
        );

        if ($compteurs === false || (int) $compteurs['candidats'] === 0) {
            return null;
        }

        $groupes = $this->connection->fetchAllAssociative(
            "SELECT g.libelle_abrev, g.legislature, COUNT(*) AS candidats,
                    $effectif AS effectif,
                    ROUND(COUNT(*) / $effectif * 100) AS pct,
                    " . CouleurGroupe::SQL . ' AS couleur
             FROM candidature c
             JOIN election e ON e.id = c.election_id
             JOIN depute d ON d.id = c.depute_id
             JOIN groupe g ON g.id = d.groupe_id
             WHERE e.slug = :slug AND c.visible = 1 AND c.candidat = 1 AND ' . $actif . '
             GROUP BY g.id
             ORDER BY pct DESC',
            ['slug' => self::MUNICIPALES, 'legislature' => Legislature::COURANTE],
        );

        return [
            'candidats' => (int) $compteurs['candidats'],
            'tetes' => (int) $compteurs['tetes'],
            'groupes' => $groupes,
        ];
    }

    /**
     * Groupes en activité et leur effectif, du plus grand au plus petit.
     * Les non-inscrits en font partie : ils occupent des sièges dans l'hémicycle.
     *
     * @return list<array<string, mixed>>
     */
    private function groupesHemicycle(int $legislature): array
    {
        // « Députés non inscrits » et non le « Non inscrit » de la base : le
        // site applique ce renommage dans toutes les requêtes de son
        // Groupes_model (CASE WHEN o.libelle = "Non inscrit"), et c'est le
        // libellé qui sort dans les bulles de l'hémicycle.
        return $this->connection->fetchAllAssociative(
            'SELECT CASE WHEN g.libelle = \'Non inscrit\' THEN \'Députés non inscrits\' ELSE g.libelle END AS libelle,
                    g.libelle_abrev, g.legislature, COUNT(d.id) AS effectif,
                    ' . CouleurGroupe::SQL . ' AS couleur
             FROM groupe g
             LEFT JOIN depute d ON d.groupe_id = g.id
             WHERE g.legislature = :legislature AND g.date_fin IS NULL
             GROUP BY g.id
             HAVING effectif > 0
             ORDER BY effectif DESC, g.libelle',
            ['legislature' => $legislature],
        );
    }

    /**
     * Effectif cumulé de chaque bloc politique.
     *
     * @param list<array<string, mixed>> $groupes
     *
     * @return array<string, int>
     */
    private function blocs(array $groupes): array
    {
        $effectifs = array_column($groupes, 'effectif', 'libelle_abrev');

        return array_map(
            static fn (array $abrevs) => (int) array_sum(array_intersect_key($effectifs, array_flip($abrevs))),
            self::BLOCS,
        );
    }

    /**
     * Groupes rangés de la gauche à la droite de l'hémicycle, pour le donut
     * Chart.js (`Groupes_model::get_groupes_sorted()`).
     *
     * @param list<array<string, mixed>> $groupes
     *
     * @return list<array<string, mixed>>
     */
    private function ordreHemicycle(array $groupes): array
    {
        $rang = array_flip(self::ORDRE_HEMICYCLE);
        $inconnu = count($rang);
        usort($groupes, static fn (array $a, array $b) => ($rang[$a['libelle_abrev']] ?? $inconnu) <=> ($rang[$b['libelle_abrev']] ?? $inconnu));

        return $groupes;
    }

    /**
     * Les trois derniers articles publiés du blog, pour le bloc « Nos analyses
     * et décryptages » — mêmes champs que les listes de BlogController.
     *
     * @return list<array<string, mixed>>
     */
    private function derniersArticles(): array
    {
        $articles = $this->connection->fetchAllAssociative(
            "SELECT a.id, a.titre, a.slug, a.image_nom, a.cree_le,
                    c.nom AS rubrique_nom, c.slug AS rubrique_slug
             FROM article a
             JOIN categorie_article c ON c.id = a.categorie_id
             WHERE a.etat = 'published'
             ORDER BY a.cree_le DESC
             LIMIT 3",
        );

        foreach ($articles as &$article) {
            // « 09 septembre 2025 » : jour sur deux chiffres, comme le site.
            $dt = new \DateTimeImmutable((string) $article['cree_le']);
            $article['date_fr'] = sprintf('%s %s %s', $dt->format('d'), DatanExtension::MOIS[(int) $dt->format('n')] ?? '', $dt->format('Y'));
        }

        return $articles;
    }

    /**
     * Derniers votes décryptés par la rédaction.
     *
     * @return list<array<string, mixed>>
     */
    private function derniersDecryptages(): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT dcr.title, dcr.legislature, dcr.vote_numero,
                    s.date_scrutin, s.sort_code,
                    c.name AS categorie_name, l.name AS lecture_name
             FROM decryptage dcr
             JOIN scrutin s ON s.id = dcr.scrutin_id
             LEFT JOIN categorie c ON c.id = dcr.categorie_id
             LEFT JOIN lecture l ON l.id = dcr.lecture_id
             WHERE dcr.state = :published
             ORDER BY dcr.legislature DESC, s.date_scrutin DESC, dcr.vote_numero DESC
             LIMIT 5',
            ['published' => 'published'],
        );

        foreach ($rows as &$row) {
            $row['date_abregee'] = $this->dateAbregee($row['date_scrutin']);
        }

        return $rows;
    }

    /**
     * Dernières explications de vote publiées par les députés.
     *
     * L'application d'origine en tire trois au hasard parmi les quinze plus
     * récentes ; on prend ici les trois plus récentes, pour que la page reste
     * identique d'un appel à l'autre et donc réellement cachable.
     *
     * @return list<array<string, mixed>>
     */
    private function dernieresExplications(): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT e.texte, d.firstname, d.lastname, d.slug, d.dpt_slug, d.mp_id,
                    g.libelle_abrev AS groupe_abrev, g.legislature AS groupe_legislature,
                    ' . CouleurGroupe::SQL . ' AS groupe_couleur,
                    dcr.title AS decryptage_title, dcr.legislature, dcr.vote_numero,
                    v.position
             FROM explication e
             JOIN depute d ON d.id = e.depute_id
             JOIN decryptage dcr ON dcr.scrutin_id = e.scrutin_id AND dcr.state = :published
             LEFT JOIN groupe g ON g.id = d.groupe_id
             LEFT JOIN vote v ON v.depute_id = e.depute_id AND v.scrutin_id = e.scrutin_id
                              AND v.vote_type = :officiel
             WHERE e.publiee = 1
             ORDER BY e.modified_at DESC, e.created_at DESC
             LIMIT 3',
            ['published' => 'published', 'officiel' => 'decompteNominatif'],
        );
    }

    private function dateAbregee(?string $date): string
    {
        if ($date === null || $date === '') {
            return '';
        }

        $dt = new \DateTimeImmutable($date);

        return sprintf('%s %s %s', $dt->format('d'), self::MOIS_ABREGES[(int) $dt->format('n')], $dt->format('Y'));
    }
}
