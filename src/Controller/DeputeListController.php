<?php

namespace App\Controller;

use App\CouleurGroupe;
use App\Enum\PopulationDeputes;
use App\Http\RechauffeurCache;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Listes de députés (Deputes::index et Deputes::inactifs de l'application
 * CodeIgniter d'origine, vue application/views/deputes/all.php).
 *
 * La page affiche jusqu'à 660 cartes : les cartes et les effectifs par groupe
 * tiennent en une seule requête agrégée, les seconds étant déduits en PHP de la
 * liste déjà chargée. Seule la répartition hommes/femmes en demande une
 * deuxième — elle ne porte pas sur la population affichée, cf. {@see genres()}.
 */
class DeputeListController extends AbstractController
{
    /** La composition de l'Assemblée ne bouge qu'au fil des nominations. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /**
     * Groupe retenu pour un député sur une législature donnée.
     *
     * Un député peut changer de groupe en cours de législature, et son groupe
     * peut lui-même être renommé (SOC est devenu SOC-A à la dissolution de la
     * NUPES, À Droite est devenu UDR puis UDDPLR) : `fonction_groupe` porte donc
     * plusieurs rattachements par député et par législature. On retient le
     * dernier en date — rattachement encore en cours d'abord, puis date de fin
     * la plus récente — ce qui est la règle de l'application d'origine
     * (`scripts/daily.php`, construction de `deputes_all` :
     * `ORDER BY !ISNULL(mg.dateFin), mg.dateFin DESC LIMIT 1`).
     *
     * Elle départage aussi les lignes « Député non-inscrit » que l'open data
     * ouvre pour chacun entre l'élection et l'entrée dans un groupe.
     *
     * `nomin_principale` écarte les rattachements secondaires : onze députés de
     * la 17e en portent un second, ouvert lui aussi, et rien dans les dates ne
     * dit lequel compte. Sur 588 rattachements de groupe ouverts, 577 sont
     * principaux — le nombre de sièges.
     */
    private const RATTACHEMENT_SQL = <<<'SQL'
        SELECT depute_id, groupe_id FROM (
            SELECT fg.depute_id, fg.groupe_id,
                   ROW_NUMBER() OVER (
                       PARTITION BY fg.depute_id
                       ORDER BY (fg.date_fin IS NOT NULL), fg.date_fin DESC, fg.date_debut DESC
                   ) AS rang
            FROM fonction_groupe fg
            JOIN groupe gl ON gl.id = fg.groupe_id AND gl.legislature = :legislature
            WHERE fg.nomin_principale = 1
        ) classement
        WHERE classement.rang = 1
        SQL;

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/deputes', name: 'deputes_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->liste(Legislature::COURANTE, PopulationDeputes::Actifs);
    }

    #[Route('/deputes/inactifs', name: 'deputes_inactifs', methods: ['GET'])]
    public function inactifs(): Response
    {
        return $this->liste(Legislature::COURANTE, PopulationDeputes::Anciens);
    }

    #[Route('/deputes/legislature-{legislature}', name: 'deputes_legislature', requirements: ['legislature' => '\d+'], methods: ['GET'])]
    public function legislature(int $legislature): Response
    {
        // La législature courante n'a qu'une URL canonique, /deputes, comme dans
        // l'application d'origine qui redirige elle aussi.
        if ($legislature === Legislature::COURANTE) {
            return $this->redirectToRoute('deputes_index', [], Response::HTTP_MOVED_PERMANENTLY);
        }

        if ($legislature < Legislature::PREMIERE) {
            throw $this->createNotFoundException('Législature inconnue.');
        }

        return $this->liste($legislature, PopulationDeputes::Tous);
    }

    private function liste(int $legislature, PopulationDeputes $population): Response
    {
        $deputes = $this->deputes($legislature, $population);

        if ($deputes === []) {
            throw $this->createNotFoundException('Aucun député pour cette législature.');
        }

        $response = $this->render('depute/all.html.twig', [
            'legislature' => $legislature,
            'legislature_courante' => Legislature::COURANTE,
            'legislatures' => Legislature::publiees(),
            'actifs' => $population === PopulationDeputes::Actifs,
            'anciens' => $population === PopulationDeputes::Anciens,
            'deputes' => $deputes,
            // Sur la page des anciens députés, l'application d'origine range les
            // filtres par libellé de groupe et non par effectif décroissant.
            'groupes' => $this->effectifs($deputes, $population === PopulationDeputes::Anciens),
            'genres' => $this->genres($legislature),
            // La page des anciens députés les a déjà tous chargés : inutile de
            // les recompter en base.
            'nombre_inactifs' => $population === PopulationDeputes::Anciens
                ? count($deputes)
                : $this->nombreInactifs($legislature),
            'fil_ariane' => $this->filAriane($legislature, $population),
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Fil d'Ariane, à l'identique du contrôleur Deputes de l'origine — y
     * compris son « ème » là où les pages de votes écrivent « e ».
     *
     * @return list<array{nom: string, url: string}>
     */
    private function filAriane(int $legislature, PopulationDeputes $population): array
    {
        $fil = [
            ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
            ['nom' => 'Députés', 'url' => $this->generateUrl('deputes_index')],
        ];

        if ($population === PopulationDeputes::Anciens) {
            $fil[] = ['nom' => 'Députés plus en activité', 'url' => $this->generateUrl('deputes_inactifs')];
        } elseif ($legislature !== Legislature::COURANTE) {
            $fil[] = ['nom' => $legislature . 'ème législature', 'url' => $this->generateUrl('deputes_legislature', ['legislature' => $legislature])];
        }

        return $fil;
    }

    /**
     * Députés d'une législature, avec le groupe auquel ils siégeaient *à cette
     * législature* — et non leur groupe actuel, que porte `depute.groupe_id`.
     *
     * L'appartenance à une législature se lit dans `mandat` : `depute` ne porte
     * pas de législature (une ligne par personne, mp_id unique).
     *
     * @return list<array<string, mixed>>
     */
    private function deputes(int $legislature, PopulationDeputes $population): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT d.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug, d.civilite,
                    d.departement_nom, d.departement_code,
                    g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                    ' . CouleurGroupe::SQL . ' AS groupe_couleur,
                    g.legislature AS groupe_legislature,
                    dl.legislature_last
             FROM depute d
             JOIN (SELECT depute_id, MAX(legislature) AS legislature_last
                   FROM mandat GROUP BY depute_id) dl ON dl.depute_id = d.id
             LEFT JOIN (' . self::RATTACHEMENT_SQL . ') rattachement ON rattachement.depute_id = d.id
             LEFT JOIN groupe g ON g.id = rattachement.groupe_id
             WHERE ' . $this->critereMandat($population) . '
             ORDER BY d.lastname, d.firstname',
            ['legislature' => $legislature],
        );
    }

    /**
     * Critère de mandat délimitant la population affichée, la table `depute`
     * étant aliasée `d`.
     */
    private function critereMandat(PopulationDeputes $population): string
    {
        $mandatDeLaLegislature = 'SELECT 1 FROM mandat m
             WHERE m.depute_id = d.id AND m.legislature = :legislature';

        return match ($population) {
            // Un mandat sans date de fin désigne un député en exercice.
            PopulationDeputes::Actifs => 'EXISTS (' . $mandatDeLaLegislature . ' AND m.date_fin IS NULL)',

            // Sur une législature achevée, tous les mandats sont clos : la
            // population affichée est celle de tous ceux qui ont siégé.
            PopulationDeputes::Tous => 'EXISTS (' . $mandatDeLaLegislature . ')',

            // Un député revenu siéger après une interruption reste un député en
            // exercice, jamais un ancien — d'où la seconde condition.
            PopulationDeputes::Anciens => 'EXISTS (' . $mandatDeLaLegislature . ' AND m.date_fin IS NOT NULL)
                 AND NOT EXISTS (' . $mandatDeLaLegislature . ' AND m.date_fin IS NULL)',
        };
    }

    /**
     * Effectif par groupe, du plus grand au plus petit, déduit de la liste
     * affichée : c'est ce que fait `get_groupes_from_mp_array()` dans
     * l'application d'origine, et cela garantit que les filtres correspondent
     * exactement aux cartes présentes sur la page.
     *
     * @param list<array<string, mixed>> $deputes
     *
     * @return list<array{libelle: string, abrev: string, couleur: string|null, effectif: int}>
     */
    private function effectifs(array $deputes, bool $parLibelle = false): array
    {
        $groupes = [];

        foreach ($deputes as $depute) {
            $abrev = $depute['groupe_abrev'];

            if ($abrev === null || $abrev === '') {
                continue;
            }

            $groupes[$abrev] ??= [
                'libelle' => $depute['groupe_libelle'],
                'abrev' => $abrev,
                'couleur' => $depute['groupe_couleur'],
                'effectif' => 0,
            ];
            ++$groupes[$abrev]['effectif'];
        }

        // À effectif égal, le libellé départage — c'est l'ordre retenu par les
        // requêtes de l'application d'origine (ORDER BY effectif DESC, libelle).
        usort(
            $groupes,
            $parLibelle
                ? static fn (array $a, array $b) => $a['libelle'] <=> $b['libelle']
                : static fn (array $a, array $b) => [$b['effectif'], $a['libelle']] <=> [$a['effectif'], $b['libelle']],
        );

        return $groupes;
    }

    /**
     * Répartition hommes/femmes annoncée par la page.
     *
     * Ce n'est **pas** la population des cartes. Sur une législature achevée la
     * page liste tous ceux qui y ont siégé — 618 pour la 16e — quand la phrase,
     * elle, parle de l'Assemblée, qui compte 577 sièges. L'application d'origine
     * compte donc la composition **au jour d'ouverture** de la législature
     * (`Deputes_model::get_deputes_gender()`, borne sur `datePriseFonction`), et
     * pour la législature en cours les mandats encore ouverts. Compter les
     * cartes affichait 380 hommes / 238 femmes là où datan.fr affiche 362 / 215.
     *
     * Reste un écart de dix personnes : notre source ne porte que 567 prises de
     * fonction au 22 juin 2022, là où datan.fr en compte 577. Les proportions,
     * elles, tombent juste (63 % / 37 %). Voir `TODO.md`.
     *
     * @return array{hommes: int, femmes: int, hommes_pct: int, femmes_pct: int}
     */
    private function genres(int $legislature): array
    {
        $ouverture = Legislature::ouverture($legislature);

        $effectifs = $this->connection->fetchAllKeyValue(
            'SELECT d.civilite, COUNT(DISTINCT d.id)
             FROM mandat m
             JOIN depute d ON d.id = m.depute_id
             WHERE m.legislature = :legislature
               AND ' . ($ouverture === null ? 'm.date_fin IS NULL' : 'm.date_prise_fonction = :ouverture') . '
             GROUP BY d.civilite',
            $ouverture === null
                ? ['legislature' => $legislature]
                : ['legislature' => $legislature, 'ouverture' => $ouverture],
        );

        $hommes = (int) ($effectifs['M.'] ?? 0);
        $femmes = (int) ($effectifs['Mme'] ?? 0);

        $total = max(1, $hommes + $femmes);

        return [
            'hommes' => $hommes,
            'femmes' => $femmes,
            'hommes_pct' => (int) round($hommes / $total * 100),
            'femmes_pct' => (int) round($femmes / $total * 100),
        ];
    }

    /**
     * Députés dont le mandat s'est achevé en cours de législature (démission,
     * nomination au Gouvernement, décès).
     *
     * Même définition que la population {@see PopulationDeputes::Anciens}, pour
     * que le nombre annoncé sur /deputes soit celui des cartes de /deputes/inactifs.
     */
    private function nombreInactifs(int $legislature): int
    {
        return (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM depute d WHERE ' . $this->critereMandat(PopulationDeputes::Anciens),
            ['legislature' => $legislature],
        );
    }
}
