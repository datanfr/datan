<?php

namespace App\Groupe;

use App\FamilleGroupe;
use App\Legislature;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Les séries que déroule la page de statistiques d'un groupe : historiques par
 * législature, évolutions mois par mois et classements entre groupes.
 *
 * L'application d'origine lit ici sept tables précalculées la nuit
 * (`class_groups`, `class_groups_month`, `groupes_stats_history`,
 * `groupes_effectif_history`…) que nous n'avons pas reprises : elles ne portent
 * aucune donnée que `vote_groupe`, `fonction_groupe` et `depute` ne contiennent
 * déjà. Tout se recalcule donc à la volée, et le cache HTTP de la page tient
 * lieu de précalcul. Les formules sont celles de `scripts/daily.php`, vérifiées
 * chiffre à chiffre contre le site.
 */
final class StatistiquesGroupe
{
    /** Sigle des non-inscrits : ils ne forment pas un groupe et sortent des classements. */
    private const NON_INSCRITS = 'NI';

    /** Position politique de la majorité présidentielle, telle que l'Assemblée la déclare. */
    private const MAJORITAIRE = 'Majoritaire';

    /**
     * Années portées par chaque législature, reprises telles quelles de
     * `Groupes_model::get_effectif_history()`.
     *
     * Six colonnes par législature, même quand elle n'a pas duré six ans : les
     * années sans effectif laissent une barre vide, et c'est ce que montre le
     * site. La 16e y court jusqu'en 2027 bien qu'elle se soit achevée en 2024 —
     * la table date d'avant la dissolution et n'a pas été retouchée.
     */
    private const ANNEES = [
        14 => [2012, 2013, 2014, 2015, 2016, 2017],
        15 => [2017, 2018, 2019, 2020, 2021, 2022],
        16 => [2022, 2023, 2024, 2025, 2026, 2027],
        17 => [2024, 2025, 2026, 2027, 2028, 2029],
    ];

    /** Indice d'accord d'une ligne de `vote_groupe`, `vg` étant l'alias attendu. */
    private const COHESION_SQL = '(GREATEST(vg.nombre_pours, vg.nombre_contres, vg.nombre_abstentions)
            - 0.5 * ((vg.nombre_pours + vg.nombre_contres + vg.nombre_abstentions)
                     - GREATEST(vg.nombre_pours, vg.nombre_contres, vg.nombre_abstentions)))
           / NULLIF(vg.nombre_pours + vg.nombre_contres + vg.nombre_abstentions, 0)';

    /** Part des membres ayant pris part au vote sur une ligne de `vote_groupe`. */
    private const PARTICIPATION_SQL = ParticipationGroupe::SQL;

    /**
     * Deux groupes sont comptés d'accord lorsqu'ils ont voté pour tous les deux
     * ou contre tous les deux : deux abstentions ne valent pas accord, et il n'y
     * a pas d'accord partiel. Règle de `daily.php`, contre-intuitive mais
     * affichée depuis toujours.
     */
    private const ACCORD_SQL = "(nous.position_majoritaire = autre.position_majoritaire
             AND nous.position_majoritaire IN ('pour', 'contre'))";

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Bornes de la législature, pour le sous-titre « 17ème législature (2024 -
     * en cours) ». Faute d'une table de législatures, elles se lisent sur les
     * groupes : ils naissent le jour de l'ouverture et meurent avec elle.
     *
     * @return array{debut: int, fin: int|null}
     */
    public function bornes(int $legislature): array
    {
        $row = $this->connection->fetchAssociative(
            'SELECT YEAR(MIN(date_debut)) AS debut,
                    CASE WHEN SUM(date_fin IS NULL) > 0 THEN NULL ELSE YEAR(MAX(date_fin)) END AS fin
             FROM groupe WHERE legislature = :legislature',
            ['legislature' => $legislature],
        ) ?: [];

        return [
            'debut' => (int) ($row['debut'] ?? 0),
            'fin' => isset($row['fin']) ? (int) $row['fin'] : null,
        ];
    }

    /**
     * Les incarnations successives du groupe — sa famille au sens de
     * {@see FamilleGroupe} — avec cohésion et participation de chacune.
     *
     * C'est la série que rendent les histogrammes « sur les dernières
     * législatures » : un groupe rebaptisé y garde sa continuité, LAREM puis RE
     * puis EPR figurant côte à côte.
     *
     * @return list<array<string, mixed>>
     */
    public function famille(string $uid): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT g.id, g.legislature, g.libelle, g.libelle_abrev, g.date_debut, g.date_fin,
                    g.position_politique,
                    COUNT(vg.id) AS scrutins,
                    ROUND(AVG(' . self::COHESION_SQL . '), 3) AS cohesion,
                    ROUND(AVG(' . self::PARTICIPATION_SQL . '), 3) AS participation
             FROM groupe g
             LEFT JOIN vote_groupe vg ON vg.groupe_id = g.id
             WHERE g.uid IN (:famille) AND g.legislature >= :premiere
             GROUP BY g.id
             ORDER BY g.legislature, g.date_debut',
            ['famille' => FamilleGroupe::pour($uid), 'premiere' => Legislature::PREMIERE],
            ['famille' => ArrayParameterType::STRING],
        );
    }

    /**
     * Proximité de chaque incarnation du groupe avec la majorité présidentielle
     * de sa propre législature, indexée par identifiant de groupe.
     *
     * La 17e législature n'en déclare aucune : la série y est vide, et c'est
     * l'avertissement « pas encore assez de données » qui s'affiche — comme sur
     * le site, qui lit la même absence.
     *
     * @param list<array<string, mixed>> $famille
     *
     * @return array<int, array{votes: int, score: float}>
     */
    public function proximiteMajorite(array $famille): array
    {
        // Sans groupe majoritaire déclaré, il n'y a rien à mesurer — et surtout
        // rien à faire chercher au moteur : la jointure sur `vote_groupe`
        // coûte plus d'une seconde avant de conclure au vide.
        $legislatures = array_filter(array_unique(array_map(intval(...), array_column($famille, 'legislature'))),
            fn (int $legislature) => $this->aUneMajorite($legislature));

        $ids = [];
        foreach ($famille as $incarnation) {
            if (\in_array((int) $incarnation['legislature'], $legislatures, true)) {
                $ids[] = (int) $incarnation['id'];
            }
        }

        if ($ids === []) {
            return [];
        }

        $lignes = $this->connection->fetchAllAssociative(
            'SELECT nous.groupe_id, COUNT(*) AS votes, ROUND(AVG(' . self::ACCORD_SQL . '), 3) AS score
             FROM vote_groupe nous
             JOIN groupe g ON g.id = nous.groupe_id
             JOIN groupe maj ON maj.legislature = g.legislature AND maj.position_politique = :majoritaire
             JOIN vote_groupe autre ON autre.scrutin_id = nous.scrutin_id AND autre.groupe_id = maj.id
             WHERE nous.groupe_id IN (:groupes)
             GROUP BY nous.groupe_id',
            ['groupes' => $ids, 'majoritaire' => self::MAJORITAIRE],
            ['groupes' => ArrayParameterType::INTEGER],
        );

        $par_groupe = [];
        foreach ($lignes as $ligne) {
            $par_groupe[(int) $ligne['groupe_id']] = [
                'votes' => (int) $ligne['votes'],
                'score' => (float) $ligne['score'],
            ];
        }

        return $par_groupe;
    }

    /**
     * Proximité moyenne des groupes de la législature avec la majorité
     * présidentielle — le repère auquel la phrase compare celle du groupe.
     *
     * Comme pour la cohésion, la moyenne se prend groupe par groupe puis se
     * moyenne : sinon un groupe qui vote souvent y pèserait plus qu'un autre.
     */
    public function proximiteMajoriteMoyenne(int $legislature): ?int
    {
        if (!$this->aUneMajorite($legislature)) {
            return null;
        }

        // La requête part des ventilations du groupe majoritaire, quelques
        // milliers de lignes, et non de celles de toute la législature : même
        // résultat, quatre fois plus vite.
        //
        // Le groupe majoritaire compte dans sa propre moyenne, et s'y compare à
        // lui-même : la table d'accords du site est une auto-jointure sans
        // garde, si bien que le couple (RE, RE) vaut 1 dès que le groupe s'est
        // exprimé. La majorité pèse donc ~100 % dans le repère, ce qui remonte
        // la moyenne de la 16e de 46 à 51 %. Écarter ce couple serait plus
        // juste, mais c'est le chiffre publié.
        $moyenne = $this->connection->fetchOne(
            'SELECT AVG(x.score) FROM (
                 SELECT ROUND(AVG(' . self::ACCORD_SQL . '), 3) AS score
                 FROM vote_groupe autre
                 JOIN groupe maj ON maj.id = autre.groupe_id
                                AND maj.legislature = :legislature AND maj.position_politique = :majoritaire
                 JOIN vote_groupe nous ON nous.scrutin_id = autre.scrutin_id
                 JOIN groupe g ON g.id = nous.groupe_id AND g.legislature = :legislature
                 GROUP BY nous.groupe_id
             ) x',
            ['legislature' => $legislature, 'majoritaire' => self::MAJORITAIRE],
        );

        return $moyenne === false || $moyenne === null ? null : (int) round((float) $moyenne * 100);
    }

    /**
     * Cohésion et participation du groupe, mois par mois.
     *
     * @return array{mois: list<string>, cohesion: list<float>, participation: list<float>}
     */
    public function comportementMensuel(int $groupeId): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            "SELECT DATE_FORMAT(s.date_scrutin, '%Y-%m') AS mois,
                    ROUND(AVG(" . self::COHESION_SQL . '), 2) AS cohesion,
                    ROUND(AVG(' . self::PARTICIPATION_SQL . ') * 100) AS participation
             FROM vote_groupe vg
             JOIN scrutin s ON s.id = vg.scrutin_id
             WHERE vg.groupe_id = :groupe
             GROUP BY mois
             ORDER BY mois',
            ['groupe' => $groupeId],
        );

        return [
            'mois' => array_column($lignes, 'mois'),
            'cohesion' => array_map(floatval(...), array_column($lignes, 'cohesion')),
            'participation' => array_map(floatval(...), array_column($lignes, 'participation')),
        ];
    }

    /**
     * Proximité du groupe avec la majorité présidentielle, mois par mois.
     *
     * @return array{mois: list<string>, valeurs: list<float>}
     */
    public function majoriteMensuelle(int $groupeId, int $legislature): array
    {
        if (!$this->aUneMajorite($legislature)) {
            return ['mois' => [], 'valeurs' => []];
        }

        $lignes = $this->connection->fetchAllAssociative(
            "SELECT DATE_FORMAT(s.date_scrutin, '%Y-%m') AS mois,
                    ROUND(AVG(" . self::ACCORD_SQL . ') * 100) AS score
             FROM vote_groupe nous
             JOIN groupe g ON g.id = nous.groupe_id
             JOIN groupe maj ON maj.legislature = g.legislature AND maj.position_politique = :majoritaire
             JOIN vote_groupe autre ON autre.scrutin_id = nous.scrutin_id AND autre.groupe_id = maj.id
             JOIN scrutin s ON s.id = nous.scrutin_id
             WHERE nous.groupe_id = :groupe
             GROUP BY mois
             ORDER BY mois',
            ['groupe' => $groupeId, 'majoritaire' => self::MAJORITAIRE],
        );

        return [
            'mois' => array_column($lignes, 'mois'),
            'valeurs' => array_map(floatval(...), array_column($lignes, 'score')),
        ];
    }

    /**
     * Effectif, âge moyen et taux de féminisation de chaque groupe en activité
     * de la législature — la matière des trois classements de la page.
     *
     * Une seule requête pour les trois : elles partagent la même population,
     * les rattachements principaux encore ouverts. Lire `depute.groupe_id`
     * donnerait zéro dès qu'un groupe est dissous.
     *
     * @return list<array<string, mixed>>
     */
    public function classements(int $legislature): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT g.id, g.libelle, g.libelle_abrev, g.legislature,
                    COUNT(DISTINCT fg.depute_id) AS effectif,
                    AVG(TIMESTAMPDIFF(YEAR, d.date_naissance, CURRENT_DATE)) AS age,
                    AVG(d.civilite = 'Mme') AS feminisation
             FROM groupe g
             JOIN fonction_groupe fg ON fg.groupe_id = g.id
                                    AND fg.nomin_principale = 1 AND fg.date_fin IS NULL
             JOIN depute d ON d.id = fg.depute_id
             WHERE g.legislature = :legislature AND g.date_fin IS NULL AND g.libelle_abrev <> :ni
             GROUP BY g.id
             ORDER BY effectif DESC, g.libelle",
            ['legislature' => $legislature, 'ni' => self::NON_INSCRITS],
        );
    }

    /**
     * Âge moyen à la fondation de chaque incarnation du groupe, indexé par
     * identifiant de groupe.
     *
     * Ce n'est pas l'âge d'aujourd'hui : le site retient la cohorte fondatrice
     * — les députés entrés le mois même de la création du groupe — et l'âge
     * qu'ils avaient à leur entrée (`daily.php`, `groupeStatsHistory`). D'où un
     * EPR à 49 ans dans l'historique et à 53 sur sa fiche.
     *
     * @param list<array<string, mixed>> $famille
     *
     * @return array<int, float>
     */
    public function ageALaFondation(array $famille): array
    {
        $ids = array_map(intval(...), array_column($famille, 'id'));
        if ($ids === []) {
            return [];
        }

        return array_map(floatval(...), $this->connection->fetchAllKeyValue(
            'SELECT g.id, ROUND(AVG(TIMESTAMPDIFF(YEAR, d.date_naissance, fg.date_debut)), 2)
             FROM groupe g
             JOIN fonction_groupe fg ON fg.groupe_id = g.id
             JOIN depute d ON d.id = fg.depute_id
             WHERE g.id IN (:groupes) AND d.date_naissance IS NOT NULL
               AND YEAR(fg.date_debut) = YEAR(g.date_debut)
               AND MONTH(fg.date_debut) = MONTH(g.date_debut)
             GROUP BY g.id',
            ['groupes' => $ids],
            ['groupes' => ArrayParameterType::INTEGER],
        ));
    }

    /**
     * Taux de féminisation de chaque incarnation du groupe, indexé par
     * identifiant de groupe.
     *
     * Un groupe encore en activité se compte sur ses membres du jour ; un
     * groupe clos, sur tous ceux qui y ont siégé — la nuance vient de
     * `daily.php` et sépare bien les 49 % d'EPR aujourd'hui des 42 % qu'a
     * pesé Renaissance sur toute la 16e législature.
     *
     * @param list<array<string, mixed>> $famille
     *
     * @return array<int, array{pct: float, n: int}>
     */
    public function feminisationParIncarnation(array $famille): array
    {
        $ids = array_map(intval(...), array_column($famille, 'id'));
        if ($ids === []) {
            return [];
        }

        // `MAX(...)` sur la civilité tient lieu de dédoublonnage : un député
        // peut porter plusieurs rattachements successifs au même groupe et ne
        // doit compter qu'une fois.
        $lignes = $this->connection->fetchAllAssociative(
            "SELECT A.groupe_id, ROUND(AVG(A.femme) * 100, 2) AS pct, SUM(A.femme) AS n
             FROM (
                 SELECT fg.groupe_id, MAX(d.civilite = 'Mme') AS femme
                 FROM fonction_groupe fg
                 JOIN groupe g ON g.id = fg.groupe_id
                 JOIN depute d ON d.id = fg.depute_id
                 WHERE fg.groupe_id IN (:groupes)
                   AND (g.date_fin IS NOT NULL OR g.legislature <> :courante OR fg.date_fin IS NULL)
                 GROUP BY fg.groupe_id, fg.depute_id
             ) A
             GROUP BY A.groupe_id",
            ['groupes' => $ids, 'courante' => Legislature::COURANTE],
            ['groupes' => ArrayParameterType::INTEGER],
        );

        $par_groupe = [];
        foreach ($lignes as $ligne) {
            $par_groupe[(int) $ligne['groupe_id']] = [
                'pct' => (float) $ligne['pct'],
                'n' => (int) $ligne['n'],
            ];
        }

        return $par_groupe;
    }

    /**
     * Effectif maximal atteint par la famille, législature par législature et
     * année par année : les barres de l'« Historique des effectifs ».
     *
     * Le site relève l'effectif chaque jour dans une table dédiée puis prend le
     * maximum de l'année. Comme le compte ne monte qu'aux dates d'entrée, il
     * suffit de l'évaluer à celles-ci — même maximum, sans balayer 3 650 jours.
     * La présidence en est exclue, comme dans `daily.php`.
     *
     * @param list<array<string, mixed>> $famille
     *
     * @return array{legislatures: list<array{legislature: int, libelle: string}>, series: list<array{valeurs: list<int|null>, annees: list<int|null>}>}
     */
    public function historiqueEffectifs(array $famille): array
    {
        $ids = array_map(intval(...), array_column($famille, 'id'));
        if ($ids === []) {
            return ['legislatures' => [], 'series' => []];
        }

        $mandats = $this->connection->fetchAllAssociative(
            "SELECT g.id AS groupe_id, g.legislature, fg.date_debut, fg.date_fin
             FROM fonction_groupe fg
             JOIN groupe g ON g.id = fg.groupe_id
             WHERE fg.groupe_id IN (:groupes) AND fg.code_qualite <> 'Président'",
            ['groupes' => $ids],
            ['groupes' => ArrayParameterType::INTEGER],
        );

        /** @var array<int, list<array{debut: string, fin: string|null}>> $parGroupe */
        $parGroupe = [];
        foreach ($mandats as $mandat) {
            $parGroupe[(int) $mandat['groupe_id']][] = [
                'debut' => (string) $mandat['date_debut'],
                'fin' => $mandat['date_fin'] === null ? null : (string) $mandat['date_fin'],
            ];
        }

        // Une législature peut porter plusieurs incarnations — À Droite, UDR et
        // UDDPLR se succèdent au sein de la 17e —, et le site retient alors la
        // plus fournie plutôt que leur somme.
        $legislatures = [];
        $maxima = [];
        foreach ($famille as $groupe) {
            $legislature = (int) $groupe['legislature'];
            $legislatures[$legislature] ??= [
                'legislature' => $legislature,
                'libelle' => $this->libelleLegislature($legislature),
            ];

            // Le relevé du site s'arrête à la disparition du groupe, ou au jour
            // même s'il vit encore : sans cette borne, un rattachement sans date
            // de fin ferait paraître des barres jusqu'en 2029.
            $fin = $groupe['date_fin'] === null ? date('Y-m-d') : (string) $groupe['date_fin'];

            foreach (self::ANNEES[$legislature] ?? [] as $rang => $annee) {
                $effectif = $this->effectifMaximal($parGroupe[(int) $groupe['id']] ?? [], $annee, $fin);
                if ($effectif !== null) {
                    $maxima[$legislature][$rang] = max($maxima[$legislature][$rang] ?? 0, $effectif);
                }
            }
        }

        ksort($legislatures);

        // Une série par rang d'année, comme les six jeux de données du site :
        // les barres d'une même législature se rangent alors côte à côte. Chaque
        // série porte aussi l'année qu'elle représente, que l'infobulle affiche
        // — l'axe ne montrant que la législature.
        $series = [];
        for ($rang = 0; $rang < 6; ++$rang) {
            $valeurs = [];
            $annees = [];
            foreach (array_keys($legislatures) as $legislature) {
                $valeurs[] = $maxima[$legislature][$rang] ?? null;
                $annees[] = self::ANNEES[$legislature][$rang] ?? null;
            }
            $series[] = ['valeurs' => $valeurs, 'annees' => $annees];
        }

        return ['legislatures' => array_values($legislatures), 'series' => $series];
    }

    /**
     * Effectif le plus élevé de l'année donnée, ou null si le groupe n'existait
     * pas encore.
     *
     * @param list<array{debut: string, fin: string|null}> $mandats
     */
    private function effectifMaximal(array $mandats, int $annee, string $fin): ?int
    {
        $premierJour = $annee . '-01-01';
        $dernierJour = min($annee . '-12-31', $fin);

        if ($dernierJour < $premierJour) {
            return null;
        }

        // Les dates candidates sont le 1er janvier et chaque entrée de l'année :
        // le compte ne peut croître qu'à ces instants-là.
        $jours = [$premierJour];
        foreach ($mandats as $mandat) {
            if ($mandat['debut'] >= $premierJour && $mandat['debut'] <= $dernierJour) {
                $jours[] = $mandat['debut'];
            }
        }

        $maximum = null;
        foreach (array_unique($jours) as $jour) {
            $effectif = 0;
            foreach ($mandats as $mandat) {
                if ($mandat['debut'] <= $jour && ($mandat['fin'] === null || $mandat['fin'] >= $jour)) {
                    ++$effectif;
                }
            }
            if ($effectif > 0) {
                $maximum = max($maximum ?? 0, $effectif);
            }
        }

        return $maximum;
    }

    /** « Leg. 17 (24 - en cours) », l'étiquette d'axe de l'historique des effectifs. */
    private function libelleLegislature(int $legislature): string
    {
        $bornes = $this->bornes($legislature);

        return sprintf(
            'Leg. %d (%s - %s)',
            $legislature,
            substr((string) $bornes['debut'], -2),
            $bornes['fin'] === null ? 'en cours' : substr((string) $bornes['fin'], -2),
        );
    }

    /** @var array<int, bool> Réponses déjà obtenues d'{@see aUneMajorite()}. */
    private array $majorites = [];

    /**
     * La législature déclare-t-elle un groupe majoritaire ?
     *
     * Depuis la dissolution de 2024, l'Assemblée ne renseigne plus de
     * `positionPolitique` : la 17e n'a donc pas de majorité présidentielle, et
     * tout ce qui s'y compare doit se taire plutôt que rendre zéro. Poser la
     * question d'abord épargne une auto-jointure de `vote_groupe` qui met plus
     * d'une seconde à conclure au vide.
     */
    private function aUneMajorite(int $legislature): bool
    {
        return $this->majorites[$legislature] ??= (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM groupe WHERE legislature = :legislature AND position_politique = :majoritaire',
            ['legislature' => $legislature, 'majoritaire' => self::MAJORITAIRE],
        );
    }
}
