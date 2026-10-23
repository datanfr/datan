<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe les résultats électoraux par commune — législatives, présidentielle
 * et européennes — depuis quatre exports de la base de l'application d'origine.
 *
 * Rien de tout cela n'est dans les dépôts des Tricoteuses : l'open data de
 * l'Assemblée publie qui siège, pas comment on a voté pour l'y envoyer.
 *
 * ```
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT year, tour, dpt, commune, circo, insee, candidate,
 *          inscrits, abs, votants, blancs, nuls, exprimes,
 *          nom, prenom, sexe, nuance, voix
 *   FROM elect_legislatives_cities
 *   ORDER BY insee, year, tour, CAST(candidate AS UNSIGNED);" > var/legacy/resultats_legislatives.tsv
 *
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT election, dpt, commune, candidate, voix, share, votants, abs_pct
 *   FROM elect_pres_2 ORDER BY dpt, commune;" > var/legacy/resultats_presidentielle.tsv
 *
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT id_liste, year, name, tete, parti
 *   FROM elect_europe_listes
 *   ORDER BY year, CAST(id_liste AS UNSIGNED);" > var/legacy/listes_europeennes.tsv
 *
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT year, dpt, commune, party, value
 *   FROM elect_europe_results ORDER BY dpt, commune, year;" > var/legacy/resultats_europeennes.tsv
 * ```
 *
 * Ce sont des données figées : un scrutin passé ne change plus. La commande
 * n'est donc pas dans `app:sync:quotidien` et ne doit pas y entrer — on la lance
 * sciemment, comme `app:scraper:scrutins`.
 */
#[AsCommand(
    name: 'app:import:resultats-electoraux',
    description: 'Importe les résultats par commune des législatives, de la présidentielle et des européennes.',
)]
class ImportResultatsElectorauxCommand extends ImportLegacyCommand
{
    private const SCRUTINS = ['legislatives', 'presidentielle', 'europeennes'];

    /**
     * « Département » des Français établis hors de France.
     *
     * Il a la longueur d'un code d'outre-mer sans en avoir les usages, et c'est
     * un piège : les replis d'écriture prévus pour l'outre-mer, appliqués ici,
     * font atterrir les résultats de huit pays sur des communes de l'Ariège —
     * `099` + `233` ne trouve rien, retombe sur `09` + `233`, et écrit sur
     * Sentenac-d'Oust. Le code de l'étranger ne s'écrit que d'une seule façon.
     */
    private const ETRANGER = '099';

    /** Collisions détaillées dans le bilan ; au-delà, seul le nombre est dit. */
    private const EXEMPLES_COLLISION = 6;

    private const COLONNES_LEGISLATIVE = [
        'annee', 'tour', 'code_insee', 'code_departement', 'circonscription', 'candidat',
        'nom', 'prenom', 'sexe', 'nuance', 'voix',
        'inscrits', 'abstentions', 'votants', 'blancs', 'nuls', 'exprimes',
    ];

    private const COLONNES_PRESIDENTIELLE = [
        'annee', 'code_insee', 'candidat', 'voix', 'part', 'votants', 'abstention_part',
    ];

    private const COLONNES_LISTE = ['annee', 'numero', 'nom', 'tete_de_liste', 'parti'];

    private const COLONNES_EUROPEENNE = ['annee', 'code_insee', 'numero_liste', 'part'];

    /**
     * Codes INSEE connus, indexés en minuscules et rendant l'orthographe exacte
     * du référentiel.
     *
     * L'indexation insensible à la casse n'est pas un excès de prudence : la
     * table `commune` écrit la Corse en minuscules (`2a004`, Ajaccio) là où
     * `departement.code` l'écrit en capitales. Comparer à l'identique fait
     * silencieusement disparaître les 360 communes corses — la base, elle, les
     * retrouve, ses collations ignorant la casse, si bien que la perte ne se voit
     * qu'en comptant.
     *
     * @var array<string, string>
     */
    private array $communes = [];

    /** @var array<string, int> lignes écartées, par motif */
    private array $ecartees = [];

    /**
     * Codes source distincts aboutissant au même code INSEE, en outre-mer.
     *
     * Ce ne sont pas des erreurs de résolution mais des doublons de la source,
     * qui note la même commune de deux façons — et l'upsert les superpose sans
     * rien dire. Les compter est le seul moyen de savoir que le total en base est
     * inférieur au nombre de lignes lues pour une raison connue.
     *
     * @var array<string, array<string, true>>
     */
    private array $collisions = [];

    protected function configure(): void
    {
        $this
            ->addOption('scrutin', null, InputOption::VALUE_REQUIRED, 'Un seul scrutin : ' . implode(', ', self::SCRUTINS))
            ->addOption('legislatives', null, InputOption::VALUE_REQUIRED, 'Export TSV de elect_legislatives_cities', 'var/legacy/resultats_legislatives.tsv')
            ->addOption('presidentielle', null, InputOption::VALUE_REQUIRED, 'Export TSV de elect_pres_2', 'var/legacy/resultats_presidentielle.tsv')
            ->addOption('listes', null, InputOption::VALUE_REQUIRED, 'Export TSV de elect_europe_listes', 'var/legacy/listes_europeennes.tsv')
            ->addOption('europeennes', null, InputOption::VALUE_REQUIRED, 'Export TSV de elect_europe_results', 'var/legacy/resultats_europeennes.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import des résultats électoraux');

        $scrutin = $input->getOption('scrutin');

        if ($scrutin !== null && !\in_array($scrutin, self::SCRUTINS, true)) {
            $io->error(sprintf('Scrutin inconnu : « %s ». Attendu : %s.', $scrutin, implode(', ', self::SCRUTINS)));

            return Command::INVALID;
        }

        foreach ($this->connection->fetchFirstColumn('SELECT code_insee FROM commune') as $code) {
            $this->communes[strtolower((string) $code)] = (string) $code;
        }

        $io->text(sprintf('%d communes au référentiel.', \count($this->communes)));

        if ($scrutin === null || $scrutin === 'legislatives') {
            $io->text(sprintf('Législatives : %d résultats.', $this->importeLegislatives((string) $input->getOption('legislatives'))));
        }

        if ($scrutin === null || $scrutin === 'presidentielle') {
            $io->text(sprintf('Présidentielle : %d résultats.', $this->importePresidentielle((string) $input->getOption('presidentielle'))));
        }

        if ($scrutin === null || $scrutin === 'europeennes') {
            $io->text(sprintf('Listes européennes : %d.', $this->importeListes((string) $input->getOption('listes'))));
            $io->text(sprintf('Européennes : %d résultats.', $this->importeEuropeennes((string) $input->getOption('europeennes'))));
        }

        $this->rapporteEcarts($io);

        $io->success(sprintf(
            '%d résultats législatifs, %d présidentiels, %d européens sur %d listes.',
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM resultat_legislative'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM resultat_presidentielle'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM resultat_europeenne'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM liste_europeenne'),
        ));

        return Command::SUCCESS;
    }

    private function importeLegislatives(string $fichier): int
    {
        $lot = [];
        $ecrits = 0;
        $misAJour = \array_slice(self::COLONNES_LEGISLATIVE, 6);

        foreach ($this->lignes($fichier) as $ligne) {
            [$annee, $tour, $dpt, $commune, $circo, $insee, $candidat,
                $inscrits, $abs, $votants, $blancs, $nuls, $exprimes,
                $nom, $prenom, $sexe, $nuance, $voix] = array_pad($ligne, 18, null);

            // Cette table porte son code INSEE, à l'inverse des deux autres.
            // Il n'est repris que s'il désigne une commune connue : les onze
            // circonscriptions de l'étranger l'ont vide, et se recomposent comme
            // ailleurs.
            $insee = $this->texte($insee);
            $insee = $insee === null ? null : ($this->communes[strtolower($insee)] ?? null);

            $insee ??= $this->insee($dpt, $commune);

            if ($insee === null) {
                $this->ecarte(strtoupper((string) $dpt) === 'ZZ'
                    ? 'électeurs hors de France'
                    : 'commune hors du référentiel');
                continue;
            }

            $lot[] = [
                $this->entier($annee), $this->entier($tour), $insee, $dpt, $this->entier($circo), $candidat,
                $this->texte($nom), $this->texte($prenom), $this->texte($sexe), $this->texte($nuance), $this->entier($voix),
                $this->entier($inscrits), $this->entier($abs), $this->entier($votants),
                $this->entier($blancs), $this->entier($nuls), $this->entier($exprimes),
            ];
            ++$ecrits;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('resultat_legislative', self::COLONNES_LEGISLATIVE, $lot, $misAJour);
                $lot = [];
            }
        }

        $this->upsert('resultat_legislative', self::COLONNES_LEGISLATIVE, $lot, $misAJour);

        return $ecrits;
    }

    private function importePresidentielle(string $fichier): int
    {
        $lot = [];
        $ecrits = 0;
        $misAJour = \array_slice(self::COLONNES_PRESIDENTIELLE, 3);

        foreach ($this->lignes($fichier) as $ligne) {
            [$annee, $dpt, $commune, $candidat, $voix, $part, $votants, $abstention] = array_pad($ligne, 8, null);

            $insee = $this->insee($dpt, $commune);

            if ($insee === null) {
                $this->ecarte(strtoupper((string) $dpt) === 'ZZ'
                    ? 'électeurs hors de France'
                    : 'commune hors du référentiel');
                continue;
            }

            $lot[] = [
                $this->entier($annee), $insee, $candidat,
                $this->entier($voix), $this->texte($part), $this->entier($votants), $this->texte($abstention),
            ];
            ++$ecrits;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('resultat_presidentielle', self::COLONNES_PRESIDENTIELLE, $lot, $misAJour);
                $lot = [];
            }
        }

        $this->upsert('resultat_presidentielle', self::COLONNES_PRESIDENTIELLE, $lot, $misAJour);

        return $ecrits;
    }

    private function importeListes(string $fichier): int
    {
        $lot = [];

        foreach ($this->lignes($fichier) as $ligne) {
            [$numero, $annee, $nom, $tete, $parti] = array_pad($ligne, 5, null);

            $lot[] = [$this->entier($annee), $this->entier($numero), $nom, $this->texte($tete), $this->texte($parti)];
        }

        $this->upsert('liste_europeenne', self::COLONNES_LISTE, $lot, \array_slice(self::COLONNES_LISTE, 2));

        return \count($lot);
    }

    private function importeEuropeennes(string $fichier): int
    {
        $lot = [];
        $ecrits = 0;

        foreach ($this->lignes($fichier) as $ligne) {
            [$annee, $dpt, $commune, $liste, $part] = array_pad($ligne, 5, null);

            $insee = $this->insee($dpt, $commune);

            if ($insee === null) {
                $this->ecarte(strtoupper((string) $dpt) === 'ZZ'
                    ? 'électeurs hors de France'
                    : 'commune hors du référentiel');
                continue;
            }

            $lot[] = [$this->entier($annee), $insee, $this->entier($liste), $this->texte($part)];
            ++$ecrits;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('resultat_europeenne', self::COLONNES_EUROPEENNE, $lot, ['part']);
                $lot = [];
            }
        }

        $this->upsert('resultat_europeenne', self::COLONNES_EUROPEENNE, $lot, ['part']);

        return $ecrits;
    }

    /**
     * Recompose le code INSEE d'une commune, que ni `elect_pres_2` ni
     * `elect_europe_results` ne portent : elles rangent leurs lignes par
     * département et par numéro de commune, à la façon des fichiers du
     * ministère.
     *
     * **C'est le référentiel qui arbitre, pas une règle de découpage.** Plusieurs
     * écritures sont essayées dans l'ordre, et la première qui désigne une
     * commune connue l'emporte ; aucun code n'est inventé. Sans cette précaution,
     * une règle positionnelle paraît juste sur la métropole et se trompe partout
     * ailleurs :
     *
     * - **Métropole** — département sur deux caractères, commune sur trois : la
     *   concaténation suffit (`01` + `001` = `01001`).
     * - **Corse** — le département est écrit en minuscules (`2a`, `2b`) là où le
     *   reste de la base l'écrit en capitales. Sans la mise en capitales, les
     *   360 communes corses sont muettes.
     * - **Français de l'étranger** — le « département » vaut `099` et le code
     *   fait six caractères : `099` + `069` désigne Dubaï. C'est la
     *   concaténation simple qui les trouve, et une règle taillée pour
     *   l'outre-mer les perdrait toutes — 15 200 lignes aux européennes.
     * - **Outre-mer** — département sur trois caractères, code de commune sur
     *   trois, et le troisième chiffre du département fait doublon avec le
     *   premier du code de commune. Les deux tables ne s'accordent pas sur celui
     *   qu'elles renseignent : la présidentielle écrit Mayotte `976` + `503`, les
     *   européennes `976` + `603`, pour la même commune `97603`. C'est donc le
     *   département qui fait foi et le premier chiffre du code de commune qu'on
     *   remplace. Le préfixe à deux caractères vient en dernier recours, pour les
     *   rares lignes que cette lecture ne place pas.
     *
     * Ces replis ne valent que pour l'outre-mer, jamais pour
     * {@see self::ETRANGER}, où ils feraient atterrir huit pays sur l'Ariège.
     *
     * `ZZ` désigne les électeurs hors de France sans les rattacher à un pays :
     * il n'y a pas de commune à recomposer, et la ligne n'a aucune page où
     * s'afficher.
     */
    private function insee(?string $departement, ?string $commune): ?string
    {
        $departement = $this->texte($departement);
        $commune = $this->texte($commune);

        if ($departement === null || $commune === null || strtoupper($departement) === 'ZZ') {
            return null;
        }

        $departement = strtoupper($departement);
        $outreMer = \strlen($departement) === 3 && $departement !== self::ETRANGER;
        $ecritures = [$departement . $commune];

        if ($outreMer) {
            $ecritures[] = $departement . substr($commune, 1);
            $ecritures[] = substr($departement, 0, 2) . $commune;
        }

        foreach ($ecritures as $ecriture) {
            $connue = $this->communes[strtolower($ecriture)] ?? null;

            if ($connue === null) {
                continue;
            }

            if ($outreMer) {
                $this->collisions[$connue][$departement . '/' . $commune] = true;
            }

            return $connue;
        }

        return null;
    }

    private function ecarte(string $motif): void
    {
        $this->ecartees[$motif] = ($this->ecartees[$motif] ?? 0) + 1;
    }

    /**
     * Les lignes non reprises, par motif.
     *
     * Elles sont rapportées plutôt que tues : une commune absente du référentiel
     * est le plus souvent une commune fusionnée depuis le scrutin, mais ce
     * pourrait être un défaut d'`app:import:communes`, et seul le chiffre permet
     * de faire la différence.
     */
    private function rapporteEcarts(SymfonyStyle $io): void
    {
        if ($this->ecartees === []) {
            return;
        }

        ksort($this->ecartees);

        foreach ($this->ecartees as $motif => $nombre) {
            $io->text(sprintf('%d lignes écartées — %s.', $nombre, $motif));
        }

        $doubles = array_filter($this->collisions, static fn (array $sources) => \count($sources) > 1);

        if ($doubles === []) {
            return;
        }

        ksort($doubles);
        $exemples = \array_slice($doubles, 0, self::EXEMPLES_COLLISION, true);

        $io->text(sprintf(
            '%d communes d\'outre-mer notées de deux façons par la source, dont les lignes se superposent : %s%s.',
            \count($doubles),
            implode(', ', array_map(
                static fn (string $insee, array $sources) => $insee . ' ← ' . implode(' et ', array_keys($sources)),
                array_keys($exemples),
                $exemples,
            )),
            \count($doubles) > \count($exemples) ? ', et ' . (\count($doubles) - \count($exemples)) . ' autres' : '',
        ));

        // Saint-Barthélemy et Saint-Martin sont le seul cas où la superposition
        // n'est pas anodine : les deux collectivités se partagent les
        // départements 977 et 978, et la source les y range indifféremment. Les
        // deux écritures désignent alors deux communes différentes, et non deux
        // graphies de la même — d'où un avertissement à part.
        $limitrophes = array_intersect_key($doubles, ['97701' => true, '97801' => true]);

        if ($limitrophes !== []) {
            $io->warning(sprintf(
                'Saint-Barthélemy (97701) et Saint-Martin (97801) sont interchangeables dans la source : '
                . '%s. Les chiffres de ces deux communes sont à vérifier avant publication.',
                implode(', ', array_keys($limitrophes)),
            ));
        }
    }
}
