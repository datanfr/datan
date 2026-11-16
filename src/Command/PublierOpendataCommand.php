<?php

namespace App\Command;

use App\Depute\Anciennete;
use App\Depute\GroupeAuScrutin;
use App\Enum\TypeClassement;
use App\Legislature;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Les quatre jeux de données que Datan publie sur data.gouv.fr depuis 2021 :
 * députés et groupes, en activité et historique. Équivalent des méthodes
 * `opendata_*` de `daily.php`, qui les récrivaient chaque jour dans
 * `assets/opendata/` puis les téléversaient sur data.gouv.
 *
 * **Les colonnes sont un contrat**, au même titre que les adresses : noms,
 * ordre, précision des nombres — et jusqu'à la coquille `socreCohesion` — sont
 * ceux du legacy, parce que des réutilisations les lisent par leur nom.
 *
 * **Deux sources de scores.** Pour les députés en exercice et les groupes en
 * activité, les scores viennent de `classement`, ceux des pages
 * /statistiques : le jeu dit ce que dit le site. Pour le reste — législatures
 * closes, députés partis, groupes dissous —, nos classements ne calculent rien,
 * et les valeurs du legacy, qui ne bougeront plus, ont été figées dans
 * `ressources/opendata/` (requêtes : `bin/recuperation/legacy/opendata_*_gel.sql`).
 * Ce qui ne se trouve dans aucune des deux reste vide, jamais deviné : un
 * député qui quitte l'hémicycle après la bascule perd ses scores de 17e.
 * Les écarts avec le legacy sont dans `DIFFERENCES.md`.
 *
 * Le téléversement n'a lieu qu'avec `--televerser` et une clé DATA_GOUV_API_KEY :
 * tant que le legacy publie, deux publieurs se remplaceraient l'un l'autre.
 */
#[AsCommand(
    name: 'app:opendata:publier',
    description: 'Écrit les jeux CSV des députés et des groupes (assets/opendata), et les téléverse sur data.gouv.fr.',
)]
class PublierOpendataCommand extends Command
{
    /** Adresse publique des fichiers : `datan.fr/assets/opendata/…`, indexée depuis 2021. */
    public const DOSSIER_PUBLIC = 'assets/opendata';

    private const API_DATA_GOUV = 'https://www.data.gouv.fr/api/1';

    /**
     * Fichier → jeu et ressource data.gouv, repris de `daily.php`. La ressource
     * se remplace en place : son adresse de téléchargement ne change pas.
     */
    private const JEUX = [
        'deputes_active.csv' => ['5fc8b732d30fbf1ed6648aab', '092bd7bb-1543-405b-b53c-932ebb49bb8e'],
        'groupes_active.csv' => ['60ed57a9f0c7c3a1eb29733f', '4612d596-9a78-4ec6-b60c-ccc1ee11f8c0'],
        'deputes-historique.csv' => ['60f2ffc8284ff5e8c1ed0655', '817fda38-d616-43e9-852f-790510f4d157'],
        'groupes-historique.csv' => ['60f30419135bec6a5e480086', '530940ab-45f3-41e3-8de3-759568c728b8'],
    ];

    /** Le legacy ne publie l'historique des groupes que depuis la 14e. */
    private const PREMIERE_LEGISLATURE_GROUPES = 14;

    /** Valeurs figées du legacy, cf. le docblock. */
    private const GEL_DEPUTES = 'ressources/opendata/gel-legacy-deputes.tsv';
    private const GEL_GROUPES = 'ressources/opendata/gel-legacy-groupes.tsv';

    public function __construct(
        private readonly Connection $connection,
        private readonly HttpClientInterface $client,
        #[Autowire('%kernel.project_dir%')] private readonly string $racineProjet,
        #[Autowire(env: 'DATA_GOUV_API_KEY')] private readonly string $cleDataGouv,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('televerser', null, InputOption::VALUE_NONE, 'Téléverse aussi les quatre fichiers sur data.gouv.fr (demande DATA_GOUV_API_KEY)')
            ->addOption('dossier', null, InputOption::VALUE_REQUIRED, 'Écrit ailleurs que dans public/assets/opendata (essai, comparaison)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Jeux de données ouverts');

        $televerser = (bool) $input->getOption('televerser');
        if ($televerser && $this->cleDataGouv === '') {
            $io->error('DATA_GOUV_API_KEY n\'est pas posée : rien ne peut être téléversé.');

            return Command::FAILURE;
        }

        $dossier = $input->getOption('dossier') ?? $this->racineProjet . '/public/' . self::DOSSIER_PUBLIC;
        if (!is_dir($dossier) && !mkdir($dossier, 0775, true) && !is_dir($dossier)) {
            throw new \RuntimeException(sprintf('Dossier « %s » impossible à créer.', $dossier));
        }

        $deputes = $this->deputes();
        $groupes = $this->groupes();
        $aujourdhui = (new \DateTimeImmutable('today'))->format('Y-m-d');

        $fichiers = [
            'deputes_active.csv' => array_map(
                static fn (array $d) => self::sans($d, 'active') + ['dateMaj' => $aujourdhui],
                array_values(array_filter($deputes, static fn (array $d) => $d['active'] === 1)),
            ),
            'groupes_active.csv' => array_map(
                static fn (array $g) => self::sans($g, 'active') + ['dateMaj' => $aujourdhui],
                array_values(array_filter($groupes, static fn (array $g) => $g['active'] === 1)),
            ),
            'deputes-historique.csv' => array_map(
                static fn (array $d) => self::renomme($d, 'legislature', 'legislatureLast') + ['dateMaj' => $aujourdhui],
                $deputes,
            ),
            'groupes-historique.csv' => array_map(
                static fn (array $g) => $g + ['dateMaj' => $aujourdhui],
                $groupes,
            ),
        ];

        $bilan = [];
        foreach ($fichiers as $nom => $lignes) {
            if ($lignes === []) {
                // Un fichier vide remplacerait sur data.gouv un jeu réutilisé :
                // mieux vaut échouer et garder celui de la veille.
                $io->error(sprintf('%s : aucune ligne. Base incomplète ? Rien n\'est écrit.', $nom));

                return Command::FAILURE;
            }
            $this->ecrire($dossier . '/' . $nom, $lignes);
            $bilan[] = [$nom, \count($lignes)];
        }
        $io->table(['Fichier', 'Lignes'], $bilan);

        if (!$televerser) {
            $io->success(sprintf('Quatre fichiers écrits dans %s.', $dossier));

            return Command::SUCCESS;
        }

        $echecs = 0;
        foreach (self::JEUX as $nom => [$jeu, $ressource]) {
            try {
                $this->televerser($dossier . '/' . $nom, $jeu, $ressource);
                $io->writeln(sprintf('  %s téléversé sur data.gouv.fr', $nom));
            } catch (\Throwable $e) {
                ++$echecs;
                $io->writeln(sprintf('  <error>%s non téléversé : %s</error>', $nom, $e->getMessage()));
            }
        }

        if ($echecs > 0) {
            $io->error(sprintf('%d fichier(s) sur 4 non téléversé(s).', $echecs));

            return Command::FAILURE;
        }

        $io->success('Quatre fichiers écrits et téléversés.');

        return Command::SUCCESS;
    }

    /**
     * Une ligne par député, à sa dernière législature — la `deputes_last` du
     * legacy —, dans l'ordre des colonnes publiées.
     *
     * @return list<array<string, mixed>>
     */
    private function deputes(): array
    {
        // Le groupe d'un député en exercice est son rattachement courant
        // (`depute.groupe_id`) ; celui d'un ancien, qui n'en a plus, est le plus
        // récent de sa dernière législature — encore ouvert d'abord, puis date de
        // fin la plus tardive (CLAUDE.md, « Rattachement d'un député »).
        $lignes = $this->connection->fetchAllAssociative(
            'WITH dernier_mandat AS (
                SELECT m.*, ROW_NUMBER() OVER (
                    PARTITION BY m.depute_id
                    ORDER BY m.legislature DESC, COALESCE(m.date_prise_fonction, m.date_debut) DESC
                ) AS rang
                FROM mandat m
            ),
            historique AS (
                SELECT depute_id, COUNT(DISTINCT legislature) AS mandats,
                       SUM(' . Anciennete::JOURS_SQL . ') AS jours,
                       MAX(date_fin IS NULL) AS actif
                FROM mandat GROUP BY depute_id
            ),
            dernier_groupe AS (
                SELECT fg.depute_id, g.legislature, fg.groupe_id, ROW_NUMBER() OVER (
                    PARTITION BY fg.depute_id, g.legislature
                    ORDER BY (fg.date_fin IS NOT NULL), fg.date_fin DESC, fg.date_debut DESC
                ) AS rang
                FROM fonction_groupe fg
                JOIN groupe g ON g.id = fg.groupe_id
                WHERE ' . GroupeAuScrutin::APPARTENANCE . '
            )
            SELECT d.id AS depute_id, d.mp_id AS id, dm.legislature, d.civilite AS civ,
                   d.lastname AS nom, d.firstname AS prenom,
                   d.ville_naissance AS villeNaissance, d.date_naissance AS naissance, d.age,
                   g.libelle AS groupe, g.libelle_abrev AS groupeAbrev,
                   COALESCE(dp.nom, dm.departement_nom) AS departementNom, dm.departement_code AS departementCode,
                   dm.circonscription AS circo, dm.date_prise_fonction AS datePriseFonction,
                   d.profession AS job, COALESCE(cd.mail_an, d.mail_an) AS mail,
                   cd.twitter, cd.facebook, cd.site_web AS website,
                   h.mandats AS nombreMandats, h.jours, h.actif AS active
            FROM depute d
            JOIN dernier_mandat dm ON dm.depute_id = d.id AND dm.rang = 1
            JOIN historique h ON h.depute_id = d.id
            LEFT JOIN dernier_groupe dg ON dg.depute_id = d.id AND dg.legislature = dm.legislature AND dg.rang = 1
            LEFT JOIN groupe g ON g.id = IF(h.actif = 1, COALESCE(d.groupe_id, dg.groupe_id), dg.groupe_id)
            LEFT JOIN contact_depute cd ON cd.depute_id = d.id
            -- Le nom du référentiel, tenu à la main : l\'open data écrit
            -- « Polynésie Française », le legacy « Val-d\'oise ».
            LEFT JOIN departement dp ON dp.code = dm.departement_code
            ORDER BY d.mp_id',
        );

        $classement = $this->scoresDeputes();
        $gel = $this->lireGel(self::GEL_DEPUTES, ['mpId', 'legislature']);

        $deputes = [];
        foreach ($lignes as $l) {
            $live = $classement[(int) $l['depute_id']] ?? null;
            // Les scores de `classement` sont ceux de la législature courante : ils
            // ne valent que pour une ligne de cette législature.
            if ((int) $l['legislature'] !== Legislature::COURANTE) {
                $live = null;
            }
            $fige = $gel[$l['id'] . '|' . $l['legislature']] ?? [];

            $deputes[] = [
                'id' => $l['id'],
                'legislature' => (int) $l['legislature'],
                'civ' => $l['civ'],
                'nom' => $l['nom'],
                'prenom' => $l['prenom'],
                'villeNaissance' => $l['villeNaissance'],
                'naissance' => $l['naissance'],
                'age' => $l['age'],
                'groupe' => $l['groupe'],
                'groupeAbrev' => $l['groupeAbrev'],
                'departementNom' => $l['departementNom'],
                'departementCode' => $l['departementCode'],
                'circo' => $l['circo'],
                'datePriseFonction' => $l['datePriseFonction'],
                'job' => $l['job'],
                'mail' => $l['mail'],
                'twitter' => $l['twitter'],
                'facebook' => $l['facebook'],
                'website' => $l['website'],
                'nombreMandats' => (int) $l['nombreMandats'],
                'experienceDepute' => Anciennete::enLettres((int) $l['jours']),
                'scoreParticipation' => self::nombre($live[TypeClassement::DeputesParticipationTous->value] ?? null, 2) ?? ($fige['participation'] ?? null),
                'scoreParticipationSpecialite' => self::nombre($live[TypeClassement::DeputesParticipationCommission->value] ?? null, 2) ?? ($fige['participationSpecialite'] ?? null),
                'scoreLoyaute' => self::nombre($live[TypeClassement::DeputesLoyaute->value] ?? null, 3) ?? ($fige['loyaute'] ?? null),
                // Aucune source vive : la 17e n'a pas de groupe majoritaire.
                'scoreMajorite' => $fige['majorite'] ?? null,
                'active' => (int) $l['active'],
            ];
        }

        return $deputes;
    }

    /**
     * Les scores de la législature courante, par député puis par type.
     *
     * @return array<int, array<string, float|string>>
     */
    private function scoresDeputes(): array
    {
        $scores = [];
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT depute_id, type, score, numerateur, denominateur FROM classement
             WHERE legislature = :legislature AND depute_id IS NOT NULL AND type IN (:types)',
            [
                'legislature' => Legislature::COURANTE,
                'types' => [
                    TypeClassement::DeputesParticipationTous->value,
                    TypeClassement::DeputesParticipationCommission->value,
                    TypeClassement::DeputesLoyaute->value,
                ],
            ],
            ['types' => ArrayParameterType::STRING],
        );
        foreach ($lignes as $l) {
            // Le rapport exact plutôt que le score stocké, déjà arrondi à trois
            // décimales : 0,1749 deviendrait 0,175 puis 0,18, quand le legacy,
            // qui arrondit une seule fois, publie 0,17 (28 députés sur 574).
            $scores[(int) $l['depute_id']][$l['type']] = (int) $l['denominateur'] > 0
                ? (int) $l['numerateur'] / (int) $l['denominateur']
                : $l['score'];
        }

        return $scores;
    }

    /**
     * Une ligne par groupe depuis la 14e, en activité ou dissous.
     *
     * @return list<array<string, mixed>>
     */
    private function groupes(): array
    {
        // Composition d'un groupe en activité : ses membres d'aujourd'hui, ceux
        // dont c'est le rattachement courant et qui siègent encore — comme le
        // legacy (`deputes_all.dateFin IS NULL`). Apparentés compris.
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT g.id AS groupe_id, g.uid AS id, g.legislature, g.libelle, g.libelle_abrev AS libelleAbrev,
                    g.libelle_abrege AS libelleAbrege, g.date_debut AS dateDebut,
                    g.position_politique AS positionPolitique, g.couleur AS couleurAssociee,
                    (g.date_fin IS NULL) AS active,
                    c.effectif, c.femmes, c.age
             FROM groupe g
             LEFT JOIN (
                SELECT d.groupe_id, COUNT(*) AS effectif,
                       SUM(d.civilite = \'Mme\') AS femmes, AVG(d.age) AS age
                FROM depute d
                WHERE EXISTS (SELECT 1 FROM mandat m WHERE m.depute_id = d.id AND m.date_fin IS NULL)
                GROUP BY d.groupe_id
             ) c ON c.groupe_id = g.id AND g.date_fin IS NULL
             WHERE g.legislature >= :premiere
             ORDER BY g.uid',
            ['premiere' => self::PREMIERE_LEGISLATURE_GROUPES],
        );

        $scores = [];
        foreach ($this->connection->fetchAllAssociative(
            'SELECT groupe_id, type, score FROM classement
             WHERE legislature = :legislature AND groupe_id IS NOT NULL',
            ['legislature' => Legislature::COURANTE],
        ) as $l) {
            $scores[(int) $l['groupe_id']][$l['type']] = $l['score'];
        }

        $gel = $this->lireGel(self::GEL_GROUPES, ['uid']);

        $groupes = [];
        foreach ($lignes as $l) {
            $actif = (int) $l['active'] === 1;
            $live = $actif ? ($scores[(int) $l['groupe_id']] ?? []) : [];
            $fige = $actif ? [] : ($gel[$l['id']] ?? []);
            $effectif = $l['effectif'] !== null ? (int) $l['effectif'] : null;

            $groupes[] = [
                'id' => $l['id'],
                'legislature' => (int) $l['legislature'],
                'libelle' => $l['libelle'],
                'libelleAbrev' => $l['libelleAbrev'],
                'libelleAbrege' => $l['libelleAbrege'],
                'dateDebut' => $l['dateDebut'],
                'positionPolitique' => $l['positionPolitique'],
                'couleurAssociee' => $l['couleurAssociee'],
                'effectif' => $actif ? $effectif : ($fige['effectif'] ?? null),
                'women' => $actif
                    ? ($effectif ? self::nombre(100 * (int) $l['femmes'] / $effectif, 2) : null)
                    : ($fige['women'] ?? null),
                'age' => $actif ? self::nombre($l['age'], 2) : ($fige['age'] ?? null),
                'scoreRose' => $actif
                    ? self::nombre($live[TypeClassement::GroupesOrigineSociale->value] ?? null, 3)
                    : ($fige['rose'] ?? null),
                'socreCohesion' => $actif
                    ? self::nombre($live[TypeClassement::GroupesCohesion->value] ?? null, 3)
                    : ($fige['cohesion'] ?? null),
                'scoreParticipation' => $actif
                    ? self::nombre($live[TypeClassement::GroupesParticipationTous->value] ?? null, 3)
                    : ($fige['participation'] ?? null),
                // La 17e n'a pas de groupe majoritaire : vide, comme chez le legacy.
                'scoreMajorite' => $actif ? null : ($fige['majorite'] ?? null),
                'active' => (int) $actif,
            ];
        }

        return $groupes;
    }

    /**
     * Les valeurs figées, indexées par les colonnes de `$cle` jointes par `|`.
     * `NULL` est la façon dont le client mariadb écrit l'absence.
     *
     * @param list<string> $cle
     *
     * @return array<string, array<string, string|null>>
     */
    private function lireGel(string $fichier, array $cle): array
    {
        $flux = fopen($this->racineProjet . '/' . $fichier, 'r');
        if ($flux === false) {
            throw new \RuntimeException(sprintf('Valeurs figées introuvables : %s.', $fichier));
        }

        $entete = (array) fgetcsv($flux, null, "\t", '"', '');
        $gel = [];
        while (($ligne = fgetcsv($flux, null, "\t", '"', '')) !== false) {
            $valeurs = array_combine($entete, array_map(
                static fn (?string $v) => $v === 'NULL' ? null : $v,
                $ligne,
            ));
            $gel[implode('|', array_map(static fn (string $c) => $valeurs[$c], $cle))] = $valeurs;
        }
        fclose($flux);

        return $gel;
    }

    /**
     * Écrit par un fichier temporaire renommé : le fichier servi n'est jamais lu
     * à moitié écrit. `fputcsv` comme le legacy, donc les mêmes guillemets — sur
     * tout champ qui contient une espace.
     *
     * @param list<array<string, mixed>> $lignes
     */
    private function ecrire(string $chemin, array $lignes): void
    {
        $temporaire = $chemin . '.tmp';
        $flux = fopen($temporaire, 'w');
        if ($flux === false) {
            throw new \RuntimeException(sprintf('Impossible d\'écrire %s.', $temporaire));
        }

        fputcsv($flux, array_keys($lignes[0]), ',', '"', '');
        foreach ($lignes as $ligne) {
            fputcsv($flux, $ligne, ',', '"', '');
        }
        fclose($flux);

        if (!rename($temporaire, $chemin)) {
            throw new \RuntimeException(sprintf('Impossible de remplacer %s.', $chemin));
        }
    }

    private function televerser(string $chemin, string $jeu, string $ressource): void
    {
        $formulaire = new FormDataPart([
            'file' => DataPart::fromPath($chemin, basename($chemin), 'text/csv'),
        ]);

        $reponse = $this->client->request(
            'POST',
            sprintf('%s/datasets/%s/resources/%s/upload/', self::API_DATA_GOUV, $jeu, $ressource),
            [
                'headers' => ['X-API-KEY' => $this->cleDataGouv] + $formulaire->getPreparedHeaders()->toArray(),
                'body' => $formulaire->bodyToIterable(),
                'timeout' => 120,
            ],
        );

        // getContent() lève sur un statut d'erreur : le message dit lequel.
        $reponse->getContent();
    }

    /** Un nombre à la précision du legacy, ou null — jamais un 0 inventé. */
    private static function nombre(mixed $valeur, int $decimales): ?string
    {
        return $valeur === null ? null : number_format((float) $valeur, $decimales, '.', '');
    }

    /**
     * @param array<string, mixed> $ligne
     *
     * @return array<string, mixed>
     */
    private static function sans(array $ligne, string $colonne): array
    {
        unset($ligne[$colonne]);

        return $ligne;
    }

    /**
     * Renomme une colonne sans la déplacer : l'ordre des colonnes est publié.
     *
     * @param array<string, mixed> $ligne
     *
     * @return array<string, mixed>
     */
    private static function renomme(array $ligne, string $ancien, string $nouveau): array
    {
        $renommee = [];
        foreach ($ligne as $cle => $valeur) {
            $renommee[$cle === $ancien ? $nouveau : $cle] = $valeur;
        }

        return $renommee;
    }
}
