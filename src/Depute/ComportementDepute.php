<?php

namespace App\Depute;

use App\Legislature;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

/**
 * Lectures de comportement politique d'un député, partagées entre la fiche
 * publique ({@see \App\Controller\DeputeController}) et la brique embarquée
 * ({@see \App\Controller\IframeController}).
 *
 * Les deux surfaces montrent les mêmes blocs — carrousel des votes décryptés,
 * jauges de participation et de loyauté, proximité par groupe — à partir des
 * mêmes requêtes. Elles vivaient jusqu'ici en double : la fiche dans son
 * contrôleur, l'iframe recopiée dans le sien faute de pouvoir toucher au
 * premier. Ce contrat de non-modification est levé ; les lectures communes
 * sont ici, en un seul endroit.
 *
 * Tout est en DBAL, tableaux associatifs — jamais d'hydratation d'entité sur
 * une page de lecture (cf. CLAUDE.md). Les statistiques comparatives (moyennes,
 * agrégation d'accord sur 1,27 M de votes) sont précalculées par
 * {@see \App\Command\CalculStatistiquesDeputesCommand} et seulement relues ici.
 */
class ComportementDepute
{
    /** Type de vote nominatif officiel (`decompteNominatif`), le seul décompté par député. */
    private const OFFICIAL = 'decompteNominatif';

    /** Qualification d'un groupe soutenant le Gouvernement (`organes.positionPolitique`). */
    private const MAJORITAIRE = 'Majoritaire';

    /** Borne haute conventionnelle d'un groupe encore en activité, pour le tri par fin de mandat. */
    private const SANS_FIN = '9999-12-31';

    /**
     * Positionnement éditorial d'un groupe sur l'échiquier gauche/centre/droite,
     * repris tel quel de `groups_position_edited()` de l'application d'origine —
     * une lecture de la rédaction, pas une donnée de l'open data. Sert la phrase
     * « … classé à droite de l'échiquier politique » de la carte de proximité.
     * L'axe majorité/opposition (`positionPolitique`), lui, n'existe plus en 17e
     * législature (cf. CLAUDE.md) et ne figure donc pas ici.
     */
    public const ECHIQUIER = [
        'FI' => 'à gauche', 'LFI' => 'à gauche', 'LFI-NUPES' => 'à gauche', 'LFI-NFP' => 'à gauche',
        'GDR' => 'à gauche', 'GDR-NUPES' => 'à gauche', 'SOC' => 'à gauche', 'SOC-A' => 'à gauche',
        'SER' => 'à gauche', 'SRC' => 'à gauche', 'NG' => 'à gauche', 'ECOLO' => 'à gauche',
        'ECOS' => 'à gauche', 'RRDP' => 'à gauche',
        'MODEM' => 'au centre', 'DEM' => 'au centre', 'LAREM' => 'au centre', 'RE' => 'au centre',
        'RENAIS' => 'au centre', 'LT' => 'au centre', 'LIOT' => 'au centre', 'EDS' => 'au centre',
        'AGIR-E' => 'au centre', 'UDI_I' => 'au centre', 'UDI-AGIR' => 'au centre', 'EPR' => 'au centre',
        'LR' => 'à droite', 'DR' => 'à droite', 'LC' => 'à droite', 'UDI' => 'à droite',
        'UDI-I' => 'à droite', 'UDI-A-I' => 'à droite', 'LES-REP' => 'à droite', 'R-UMP' => 'à droite',
        'UMP' => 'à droite', 'RN' => 'à droite', 'HORIZONS' => 'à droite', 'HOR' => 'à droite',
        'AD' => 'à droite', 'UDR' => 'à droite', 'UDDPLR' => 'à droite',
    ];

    /**
     * Les « positions importantes » ne sont pas des votes décryptés quelconques :
     * c'est une sélection éditoriale figée de scrutins marquants, codée en dur dans
     * `Votes_model::get_key_votes_mp()`, avec sa reformulation propre. On la reprend
     * telle quelle — sélection ET textes suivent la rédaction, pas nous.
     *
     * État du 4 août 2026, vérifié sur le site vivant : quatre scrutins, tous de
     * la 17e législature, rendus par numéro croissant — l'ordre dans lequel le
     * site les sert (sa requête sans ORDER BY sort les lignes de `votes_scores`
     * dans l'ordre d'insertion, qui est celui des numéros). Une sélection
     * antérieure portait deux scrutins de la 16e (IVG, immigration) : la
     * rédaction les a retirés, ce qui a éteint au passage la préposition doublée
     * « en faveur de du projet de loi immigration » — la garde de composition
     * reste dans `_positions.html.twig`, pour le jour où un texte en « du … »
     * reviendra.
     */
    private const VOTES_CLES = [
        ['legislature' => 17, 'numero' => 3260, 'texte' => 'la proposition du RN visant à dénoncer les accords franco-algériens de 1968'],
        ['legislature' => 17, 'numero' => 3300, 'texte' => 'la taxe Zucman sur les patrimoines supérieurs à 100 millions d\'euros'],
        ['legislature' => 17, 'numero' => 8280, 'texte' => "la proposition de loi créant un droit à l'aide à mourir"],
        ['legislature' => 17, 'numero' => 8427, 'texte' => "la loi d'urgence agricole, qui permet la réintroduction de deux pesticides néonicotinoïdes"],
    ];

    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * Votes décryptés sur lesquels le député s'est prononcé, avec son
     * explication de vote quand il en a publié une.
     *
     * Un scrutin où il n'a pas de ligne `vote` n'apparaît pas : l'application
     * d'origine écarte aussi les non-votants (`vs.vote != 'nv'`), une absence
     * n'étant pas une position.
     *
     * Le carrousel de la fiche (`_votes.php`, `get_votes_datan_depute($mp, 5)`)
     * n'en veut que les cinq plus récents ; la page « tous ses votes » les veut
     * tous. Même requête, un `LIMIT` optionnel les sépare.
     *
     * @return list<array<string, mixed>>
     */
    public function votesDecryptes(int $deputeId, ?int $limite = null): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT dcr.title, dcr.legislature, dcr.vote_numero,
                    c.name AS categorie_name, c.slug AS categorie_slug,
                    l.name AS lecture_name,
                    s.date_scrutin, v.position,
                    e.texte AS explication
             FROM decryptage dcr
             JOIN scrutin s ON s.id = dcr.scrutin_id
             JOIN vote v ON v.scrutin_id = s.id AND v.depute_id = :depute
             LEFT JOIN categorie c ON c.id = dcr.categorie_id
             LEFT JOIN lecture l ON l.id = dcr.lecture_id
             LEFT JOIN explication e ON e.scrutin_id = s.id AND e.depute_id = :depute AND e.publiee = 1
             WHERE dcr.state = :published AND v.position IN (\'pour\', \'contre\', \'abstention\')
             ORDER BY s.date_scrutin DESC'
             . ($limite !== null ? ' LIMIT ' . $limite : ''),
            ['depute' => $deputeId, 'published' => 'published'],
        );
    }

    /**
     * Positions du député sur les votes décryptés par la rédaction : ce sont les
     * scrutins que le site met en avant comme les plus parlants.
     *
     * @return list<array<string, mixed>>
     */
    public function positionsCles(int $deputeId): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT dcr.title, dcr.legislature, dcr.vote_numero, c.name AS categorie_name,
                    v.position, s.sort_code, v.scrutin_date
             FROM decryptage dcr
             JOIN scrutin s ON s.id = dcr.scrutin_id
             JOIN vote v ON v.scrutin_id = s.id AND v.depute_id = :depute AND v.vote_type = :type
             LEFT JOIN categorie c ON c.id = dcr.categorie_id
             WHERE dcr.state = :published AND v.position IN (\'pour\', \'contre\')
             ORDER BY v.scrutin_date DESC
             LIMIT 6',
            ['depute' => $deputeId, 'type' => self::OFFICIAL, 'published' => 'published'],
        );
    }

    /**
     * Le bloc « Ses positions importantes » (`_key_positions.php`) : la sélection
     * éditoriale figée {@see self::VOTES_CLES}, avec la position du député sur
     * chacun de ces scrutins et le fait qu'il ait voté ou non comme son groupe.
     *
     * La loyauté (« a voté comme son groupe ») se lit sur la position majoritaire
     * du groupe **au moment du scrutin**, résolu par `fonction_groupe` à la date
     * du vote — comme le `mandat_groupe` du legacy (daily.php:1966). Prendre le
     * groupe d'aujourd'hui casserait les changeurs de groupe — et toute future
     * sélection qui repiocherait dans une législature passée.
     *
     * L'ordre éditorial de {@see self::VOTES_CLES} est conservé ; un scrutin sans
     * vote nominatif pour ce député est simplement omis.
     *
     * **Le non-votant est rendu « abstention »**, comme à l'origine. `votes_scores`
     * y est un `varchar` où le non-votant s'écrit `'nv'` ; le `CASE WHEN vs.vote = 0
     * THEN "abstention"` de `get_key_votes_mp()` compare une chaîne à un entier,
     * MariaDB convertit `'nv'` en `0`, et les 28 non-votants des cinq scrutins clés
     * ressortent donc « ABSTENTION ». Reprendre notre `nonVotant` tel quel afficherait
     * un badge sans style — `sort-nonVotant` n'existe dans aucune des deux feuilles —
     * au-dessus d'une phrase qui, elle, dit déjà « s'est abstenu ».
     *
     * La normalisation ne vaut que pour l'affichage : `comme_groupe` se décide sur la
     * position BRUTE. Sans quoi un non-votant dont le groupe s'est majoritairement
     * abstenu passerait pour loyal, alors que l'origine met `scoreLoyaute` à `NULL`
     * dès que le vote est `'nv'` (daily.php:1981) et écrit « n'a pas voté comme son
     * groupe ».
     *
     * @return list<array{legislature: int, numero: int, texte: string, position: string, comme_groupe: bool}>
     */
    public function positionsImportantes(int $deputeId): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT s.legislature, s.numero, v.position,
                    vg.position_majoritaire AS position_groupe
             FROM vote v
             JOIN scrutin s ON s.id = v.scrutin_id
             LEFT JOIN fonction_groupe fg ON fg.depute_id = v.depute_id AND fg.nomin_principale = 1
               AND fg.date_debut <= s.date_scrutin
               AND (fg.date_fin IS NULL OR fg.date_fin >= s.date_scrutin)
             LEFT JOIN vote_groupe vg ON vg.scrutin_id = s.id AND vg.groupe_id = fg.groupe_id
             WHERE v.depute_id = :depute AND v.vote_type = :type
               AND s.legislature = 17 AND s.numero IN (3260, 3300, 8280, 8427)',
            ['depute' => $deputeId, 'type' => self::OFFICIAL],
        );

        $parCle = [];
        foreach ($lignes as $ligne) {
            $parCle[$ligne['legislature'] . '-' . $ligne['numero']] = $ligne;
        }

        $positions = [];
        foreach (self::VOTES_CLES as $cle) {
            $ligne = $parCle[$cle['legislature'] . '-' . $cle['numero']] ?? null;
            if ($ligne === null) {
                continue;
            }

            $positions[] = [
                'legislature' => $cle['legislature'],
                'numero' => $cle['numero'],
                'texte' => $cle['texte'],
                'position' => \in_array($ligne['position'], ['pour', 'contre'], true)
                    ? (string) $ligne['position']
                    : 'abstention',
                'comme_groupe' => $ligne['position_groupe'] !== null
                    && $ligne['position'] === $ligne['position_groupe'],
            ];
        }

        return $positions;
    }

    /**
     * Statistiques de comportement de la fiche, lues sur les tables précalculées
     * par {@see \App\Command\CalculStatistiquesDeputesCommand}. Reproduit
     * `Depute_service::get_statistics()` : les cartes de participation, de loyauté
     * et de proximité avec la majorité ne s'affichent qu'au-delà de dix votes
     * (`votesN >= 10`), et comparent le score du député à la moyenne de tous les
     * députés et de son groupe. Rendu null si le député n'a pas de ligne pour la
     * législature demandée — les quatre législatures publiées (14 à 17) en portent, chacune
     * calculée par `app:calcul:statistiques-deputes --legislature=N` après
     * l'import de ses votes nominatifs.
     *
     * @return array<string, mixed>|null
     */
    public function statistiques(int $deputeId, ?int $groupeId, int $legislature): ?array
    {
        $stat = $this->connection->fetchAssociative(
            'SELECT participation_score, participation_votes, loyaute_score, loyaute_votes,
                    majorite_score, majorite_votes
             FROM statistique_depute WHERE depute_id = :depute AND legislature = :legislature',
            ['depute' => $deputeId, 'legislature' => $legislature],
        );

        if ($stat === false) {
            return null;
        }

        $resultat = [];

        if ($stat['participation_score'] !== null && (int) $stat['participation_votes'] >= 10) {
            $score = (int) $stat['participation_score'];
            $moyennes = $this->moyennesStatistique('participation_score', $groupeId, $legislature);
            $resultat['participation'] = [
                'score' => $score,
                'all' => $moyennes['all'],
                'group' => $moyennes['group'],
                'edito_all' => $this->comparerStatistique($score, $moyennes['all'], 'souvent'),
                'edito_group' => $this->comparerStatistique($score, $moyennes['group'], 'souvent'),
            ];
        }

        if ($stat['loyaute_score'] !== null && (int) $stat['loyaute_votes'] >= 10) {
            $score = (int) $stat['loyaute_score'];
            $moyennes = $this->moyennesStatistique('loyaute_score', $groupeId, $legislature);
            $resultat['loyaute'] = [
                'score' => $score,
                'all' => $moyennes['all'],
                'group' => $moyennes['group'],
                'edito_all' => $this->comparerStatistique($score, $moyennes['all'], 'eleve'),
                'edito_group' => $this->comparerStatistique($score, $moyennes['group'], 'eleve'),
                // Le repli « Ce score ne prend en compte que le dernier groupe… » :
                // la loyauté du député envers chacun de ses rattachements successifs
                // (deputes_loyaute par mandatId chez l'origine, qui l'affiche même
                // pour un député resté fidèle à un seul groupe).
                'historique' => $this->loyauteParRattachement($deputeId, $legislature),
            ];
        }

        $majorite = $this->proximiteMajorite($stat, $groupeId, $legislature);
        if ($majorite !== null) {
            $resultat['majorite'] = $majorite;
        }

        $accord = $this->accordGroupes($deputeId, $groupeId, $legislature);
        if ($accord !== null) {
            $resultat['accord'] = $accord;
        }

        return $resultat !== [] ? $resultat : null;
    }

    /**
     * La carte « Proximité avec la majorité gouvernementale »
     * (`_majority_alignment.php`, alimentée par `class_majorite`).
     *
     * Trois conditions la font apparaître, toutes reprises de l'origine :
     *
     * - la législature déclare un groupe majoritaire. **La 17e n'en a pas** :
     *   depuis la dissolution de 2024 l'Assemblée ne qualifie plus ses groupes
     *   (cf. CLAUDE.md), et le site masque d'ailleurs la carte pour elle par une
     *   condition écrite en toutes lettres dans sa vue. Rien ne se rabat sur un
     *   groupe choisi au jugé ;
     * - le député n'appartient pas lui-même à la majorité : se comparer à son
     *   propre groupe ne dit rien, et la loyauté le dit déjà mieux ;
     * - il a au moins dix votes comparables, seuil commun aux trois cartes.
     *
     * La moyenne de référence — « la moyenne des députés **non membres de la
     * majorité** » — écarte les groupes majoritaires, alors que celle du groupe
     * se prend sur le groupe du député comme partout ailleurs.
     *
     * Le groupe nommé dans la phrase est « le plus gros de la majorité » selon
     * l'origine, qui prend en fait le dernier en date (`get_majority_group`) —
     * ce qui ne se voit qu'en 14e, la seule à en déclarer deux qui se succèdent :
     * SER, et non SRC, malgré ses 1 275 scrutins contre 79.
     *
     * @param array<string, mixed> $stat
     *
     * @return array<string, mixed>|null
     */
    private function proximiteMajorite(array $stat, ?int $groupeId, int $legislature): ?array
    {
        if ($stat['majorite_score'] === null || (int) $stat['majorite_votes'] < 10) {
            return null;
        }

        $majoritaires = $this->connection->fetchAllAssociative(
            'SELECT id, libelle, libelle_abrev FROM groupe
             WHERE legislature = :legislature AND position_politique = :majoritaire
             ORDER BY COALESCE(date_fin, :sansFin) DESC
             LIMIT 1',
            ['legislature' => $legislature, 'majoritaire' => self::MAJORITAIRE, 'sansFin' => self::SANS_FIN],
        );

        if ($majoritaires === [] || $groupeId === null) {
            return null;
        }

        // Tous les groupes majoritaires de la législature, pas seulement celui
        // que la phrase nomme : en 14e, un député SRC comme un député SER est
        // membre de la majorité, et sa fiche n'a pas cette carte.
        $idsMajoritaires = array_map('intval', $this->connection->fetchFirstColumn(
            'SELECT id FROM groupe WHERE legislature = :legislature AND position_politique = :majoritaire',
            ['legislature' => $legislature, 'majoritaire' => self::MAJORITAIRE],
        ));

        if (\in_array($groupeId, $idsMajoritaires, true)) {
            return null;
        }

        $score = (int) $stat['majorite_score'];
        $moyennes = $this->moyennesStatistique('majorite_score', $groupeId, $legislature, $idsMajoritaires);

        return [
            'score' => $score,
            'all' => $moyennes['all'],
            'group' => $moyennes['group'],
            'edito_all' => $this->comparerStatistique($score, $moyennes['all'], 'proche'),
            'edito_group' => $this->comparerStatistique($score, $moyennes['group'], 'proche'),
            'groupe' => $majoritaires[0],
        ];
    }

    /**
     * Loyauté du député envers chaque groupe auquel il a appartenu pendant la
     * législature : ses votes exprimés pendant chaque rattachement principal
     * (`fonction_groupe`), confrontés à la position majoritaire de CE groupe.
     * Reproduit `deputes_loyaute` agrégée par `mandatId` (daily.php:2501-2513).
     * Volume : les votes d'un seul député — la fiche est en cache une heure.
     *
     * @return list<array{libelle: string, libelle_abrev: string, score: int}>
     */
    private function loyauteParRattachement(int $deputeId, int $legislature): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT g.libelle, g.libelle_abrev,
                    ROUND(AVG(v.position = vg.position_majoritaire) * 100) AS score
             FROM fonction_groupe fg
             JOIN groupe g ON g.id = fg.groupe_id AND g.legislature = :legislature
             JOIN vote v ON v.depute_id = fg.depute_id AND v.vote_type = :type
               AND v.position IN ('pour', 'contre', 'abstention')
             JOIN scrutin s ON s.id = v.scrutin_id
               AND s.date_scrutin >= fg.date_debut
               AND (fg.date_fin IS NULL OR s.date_scrutin <= fg.date_fin)
             JOIN vote_groupe vg ON vg.scrutin_id = v.scrutin_id AND vg.groupe_id = fg.groupe_id
             WHERE fg.depute_id = :depute AND fg.nomin_principale = 1
             GROUP BY fg.id, g.libelle, g.libelle_abrev
             ORDER BY MIN(fg.date_debut)",
            ['depute' => $deputeId, 'legislature' => $legislature, 'type' => self::OFFICIAL],
        );
    }

    /**
     * Moyenne d'une statistique sur tous les députés, et sur les seuls députés
     * du groupe. `$colonne` est une valeur interne (`participation_score` ou
     * `loyaute_score`), jamais une entrée utilisateur.
     *
     * Le filtre « en exercice » ne vaut que pour la législature courante : sur
     * une législature close, plus personne ne siège et la moyenne se prend sur
     * tous ceux qui y ont voté — c'est la condition
     * `if ($legislature == legislature_current())` que le legacy pose devant
     * chacun de ses `where('active', 1)` (`get_stats_participation_solennels_all`).
     *
     * Le groupe se lit sur `statistique_depute.groupe_id`, résolu par la
     * commande de calcul pour la législature de la ligne — `depute.groupe_id`
     * ne porte que l'appartenance courante, et sur une législature passée la
     * moyenne du groupe sortait vide.
     *
     * `$exclus` retire des groupes de la moyenne « tous députés » : la carte de
     * la majorité se compare aux seuls députés qui n'en sont pas membres
     * (`get_stats_majorite_all`). Un député sans groupe pour la législature en
     * sort aussi — `NOT IN` sur un `NULL` ne retient rien, et c'est bien ce que
     * fait le `where_not_in` de l'origine sur son `LEFT JOIN deputes_all`.
     *
     * @param list<int> $exclus
     *
     * @return array{all: int|null, group: int|null}
     */
    private function moyennesStatistique(string $colonne, ?int $groupeId, int $legislature, array $exclus = []): array
    {
        $enExercice = $legislature === Legislature::COURANTE ? ' AND actif = 1' : '';

        $horsMajorite = '';
        $params = ['legislature' => $legislature];
        $types = [];
        if ($exclus !== []) {
            $horsMajorite = ' AND groupe_id NOT IN (:exclus)';
            $params['exclus'] = $exclus;
            $types['exclus'] = ArrayParameterType::INTEGER;
        }

        $all = $this->connection->fetchOne(
            "SELECT ROUND(AVG($colonne)) FROM statistique_depute
             WHERE legislature = :legislature AND $colonne IS NOT NULL" . $enExercice . $horsMajorite,
            $params,
            $types,
        );

        $group = $groupeId === null ? null : $this->connection->fetchOne(
            "SELECT ROUND(AVG($colonne)) FROM statistique_depute
             WHERE legislature = :legislature AND $colonne IS NOT NULL
               AND groupe_id = :groupe" . $enExercice,
            ['legislature' => $legislature, 'groupe' => $groupeId],
        );

        return [
            'all' => $all !== false && $all !== null ? (int) $all : null,
            'group' => $group !== false && $group !== null ? (int) $group : null,
        ];
    }

    /**
     * « plus/moins/autant » du député face à une moyenne (`Depute_edito`). La
     * loyauté se dit « plus/moins élevé », la participation « plus/moins
     * souvent », la majorité « plus/moins proche ».
     */
    private function comparerStatistique(?int $score, ?int $moyenne, string $registre): ?string
    {
        if ($score === null || $moyenne === null) {
            return null;
        }

        if ($registre === 'souvent') {
            return $score < $moyenne ? 'moins souvent' : ($score > $moyenne ? 'plus souvent' : 'autant');
        }

        if ($registre === 'proche') {
            return $score < $moyenne ? 'moins proche' : ($score > $moyenne ? 'plus proche' : 'aussi proche');
        }

        return $score < $moyenne ? 'moins élevé' : ($score > $moyenne ? 'plus élevé' : 'aussi élevé');
    }

    /**
     * Proximité du député avec chaque groupe (`deputes_accord_cleaned`).
     *
     * Pour les barres, sur la législature courante : les groupes encore actifs,
     * hors non-inscrits, au-delà de dix votes comparables, triés par proximité
     * décroissante — puis les trois du haut (« souvent ») et les trois du bas
     * (« rarement »), comme le service d'origine. Pour la phrase éditoriale :
     * le groupe le plus proche et le moins proche AUTRES que le sien. Pour le
     * classement dépliable : tous les groupes, dissous compris.
     *
     * Sur une législature close, tous ses groupes sont éteints : les barres se
     * prennent alors sur la liste complète — dissous, non-inscrits et petits
     * dénominateurs compris — et la phrase éditoriale disparaît. C'est la
     * branche « LEGISLATURE 14 » de `Depute_service::get_statistics()`, qui
     * remplace `get_accord_groupes_actifs` par `get_accord_groupes_all` et ne
     * calcule pas de positionnement.
     *
     * @return array<string, mixed>|null
     */
    private function accordGroupes(int $deputeId, ?int $groupeId, int $legislature): ?array
    {
        $courante = $legislature === Legislature::COURANTE;

        $tous = $this->connection->fetchAllAssociative(
            "SELECT g.id, g.libelle, g.libelle_abrev, g.couleur, a.accord, a.votes_n,
                    CASE WHEN g.date_fin IS NULL THEN 0 ELSE 1 END AS dissous
             FROM accord_groupe a
             JOIN groupe g ON g.id = a.groupe_id
             WHERE a.depute_id = :depute AND a.legislature = :legislature
             ORDER BY a.accord DESC, g.libelle_abrev",
            ['depute' => $deputeId, 'legislature' => $legislature],
        );

        $barres = $courante
            ? array_values(array_filter(
                $tous,
                static fn (array $g) => !$g['dissous'] && $g['libelle_abrev'] !== 'NI' && (int) $g['votes_n'] > 10,
            ))
            : $tous;

        if ($barres === []) {
            return null;
        }

        // Découpage du legacy : moitié haute puis trois premiers, moitié basse puis
        // trois derniers (rendus dans l'ordre croissant pour la seconde barre).
        $moitie = (int) round(\count($barres) / 2, 0, \PHP_ROUND_HALF_UP);
        $premiers = \array_slice(\array_slice($barres, 0, $moitie), 0, 3);

        // Les « derniers » se prennent sur un tri croissant refait avec le même
        // départage par sigle, et non en retournant la fin du tri décroissant :
        // un ex æquo à cheval sur la coupe changeait le groupe montré. Bernalicis
        // en 16e — HOR 19, RE 19, DEM 20, LR 20 — doit rendre HOR, RE, DEM comme
        // le site ; le retournement donnait RE, HOR, LR (l'ex æquo sans départage
        // du TODO §4, départagé par sigle comme partout).
        $croissant = $barres;
        usort($croissant, static fn (array $a, array $b) => [(int) $a['accord'], $a['libelle_abrev']] <=> [(int) $b['accord'], $b['libelle_abrev']]);
        $derniers = \array_slice($croissant, 0, min(3, max(0, \count($barres) - $moitie)));

        // Phrase éditoriale : plus proche et moins proche hors de son propre
        // groupe — législature courante seulement, comme le site. Son départage
        // d'ex æquo N'est PAS celui des barres : la phrase passe par le
        // `array_multisort` de `Depute_service`, qui sur accord égal compare
        // les lignes entières et retombe donc sur le libellé, en ordre
        // d'OCTETS — une initiale accentuée (« Écologiste… », É = 0xC3 0x89)
        // pèse plus lourd que tout l'ASCII. C'est ce qui fait dire au site que
        // Buisson est le moins proche d'ECOS, à 19 % ex æquo avec GDR et
        // LFI-NFP. (Les barres, elles, sortent du résultat SQL brut : leurs ex
        // æquo suivent l'ordre physique de sa table — un accident de stockage
        // qu'on ne reproduit pas, départagé ici par sigle.)
        $proximite = null;
        if ($courante) {
            $autres = array_values(array_filter($barres, fn (array $g) => (int) $g['id'] !== $groupeId));
            usort($autres, static fn (array $a, array $b) => [(int) $b['accord'], (string) $a['libelle']] <=> [(int) $a['accord'], (string) $b['libelle']]);
            if ($autres !== []) {
                $plusProche = $autres[0];
                $moinsProche = $autres[\count($autres) - 1];
                $proximite = [
                    'plus_proche' => $plusProche + ['echiquier' => self::ECHIQUIER[$plusProche['libelle_abrev']] ?? null],
                    'moins_proche' => $moinsProche + ['echiquier' => self::ECHIQUIER[$moinsProche['libelle_abrev']] ?? null],
                ];
            }
        }

        return [
            'premiers' => $premiers,
            'derniers' => $derniers,
            'proximite' => $proximite,
            'tous' => $tous,
        ];
    }
}
