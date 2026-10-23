<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Importe la géographie électorale — départements et communes — depuis deux
 * exports de la base de l'application d'origine.
 *
 * Ces données ne sont pas dans les dépôts des Tricoteuses : l'open data de
 * l'Assemblée s'arrête au numéro de circonscription et ne dit ni quelles
 * communes la composent, ni comment le site écrit « du Nord ». La base de
 * production les tient dans `departement`, `circos` (découpage) et `cities`
 * (référentiel INSEE), et c'est de là qu'elles viennent.
 *
 * Elle n'est pas joignable depuis l'application — le port 3307 de l'hôte est
 * pris par la base de développement, la production ne vit que dans le conteneur
 * `datan-db`. On passe donc par deux fichiers, régénérés ainsi :
 *
 * ```
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT departement_code, departement_nom, slug, libelle_1, libelle_2, region
 *   FROM departement ORDER BY departement_code;" > var/legacy/departements.tsv
 *
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT c.insee, c.dpt,
 *          COALESCE(NULLIF(c.commune_nom, '0'), ci.nom_standard) AS commune_nom,
 *          CASE c.insee
 *            WHEN '39217' THEN 'etoile'
 *            WHEN '07165' THEN 'nonieres'
 *            ELSE COALESCE(NULLIF(c.commune_slug, '0'), LOWER(ci.nom_sans_accent))
 *          END AS commune_slug,
 *          MAX(ci.population) AS population,
 *          GROUP_CONCAT(DISTINCT c.circo ORDER BY CAST(c.circo AS UNSIGNED)) AS circos,
 *          MAX(i.postal) AS code_postal,
 *          MAX(cin.pop2012) AS population_2012
 *   FROM circos c
 *   LEFT JOIN cities ci ON ci.code_insee = c.insee
 *   LEFT JOIN insee i ON i.insee = c.insee
 *   LEFT JOIN cities_infos cin ON cin.insee = c.insee
 *   GROUP BY c.insee, c.dpt, commune_nom, commune_slug
 *   ORDER BY c.dpt, commune_nom;" > var/legacy/communes.tsv
 *
 * Les `NULLIF(…, '0')` ne sont pas décoratifs : dans la copie dont nous
 * disposons, les deux communes nommées « Faux » (08165 dans les Ardennes,
 * 24177 en Dordogne) portent `commune_nom = '0'` et `commune_slug = '0'` —
 * « Faux » est le libellé français du booléen FALSE, un aller-retour par un
 * tableur les a convertis. La production réelle, elle, sert bien
 * `/elections/resultats/dordogne-24/ville_faux` : la corruption n'existe que
 * dans le dump. Sans cette garde, la commune sortait sous l'adresse
 * `ville_0` (annoncée telle quelle par le sitemap) et l'URL du legacy
 * rendait 404. `cities.nom_standard` est sain pour ces deux lignes, et
 * `LOWER(nom_sans_accent)` redonne exactement le slug que la production
 * sert (« faux »).
 *
 * Le `CASE` sur Étoile (L') et Nonières (Les) compense un autre décalage du
 * dump : la production a nettoyé ces deux slugs **après** la prise de notre
 * copie — datan.fr sert et annonce `ville_etoile` et `ville_nonieres`, quand
 * le dump garde `etoile-(l)` et `nonieres-(les)`. L'adresse du site est le
 * contrat : c'est sa page département qui fait foi (vérifié le 2026-08-08 sur
 * les cinq départements concernés). Ne pas généraliser aux 22 autres communes
 * à slug parenthésé : Assions (Les), Ollières-sur-Eyrieux (Les) et
 * Bonvillers (Mont) sont liées AVEC leurs parenthèses par datan.fr (qui les
 * refuse ensuite en 400 — défaut du legacy, pas un contrat), et les autres ne
 * sont liées nulle part.
 *
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT DISTINCT a.insee, a.adjacente
 *   FROM cities_adjacentes a JOIN circos c ON c.insee = a.adjacente
 *   ORDER BY a.insee, a.adjacente;" > var/legacy/communes_adjacentes.tsv
 *
 * docker exec datan-db mariadb -h 127.0.0.1 -u datan -pdatan datan \
 *   --default-character-set=utf8mb4 -B -e "
 *   SELECT insee, nameFirst, nameLast, gender
 *   FROM cities_mayors ORDER BY insee;" > var/legacy/maires.tsv
 * ```
 *
 * Le maire (`cities_mayors`) a longtemps manqué : la table est **vide dans la
 * copie de travail**, ce qui avait fait conclure à une donnée non récupérable
 * et laisser de côté le paragraphe « Le maire de X est Y. » des fiches de
 * ville. Elle est en réalité complète (34 874 communes) dans le jeu public
 * `datan.fr/assets/dataset_backup/general/latest.sql`, d'où viennent les
 * chiffres ci-dessus. La requête reste écrite contre `datan` : au déploiement,
 * c'est la vraie base qu'on interroge, pas le jeu public.
 */
#[AsCommand(
    name: 'app:import:communes',
    description: 'Importe les départements et les communes depuis un export de la base de production.',
)]
class ImportCommunesCommand extends ImportLegacyCommand
{
    private const COLONNES_DEPARTEMENT = ['code', 'nom', 'slug', 'libelle_dans', 'libelle_de', 'region'];

    private const COLONNES_COMMUNE = ['code_insee', 'nom', 'slug', 'population', 'population2012', 'code_postal', 'departement_id'];

    private const COLONNES_CIRCONSCRIPTION = ['commune_id', 'circonscription'];

    private const COLONNES_ADJACENTE = ['commune_id', 'adjacente_id'];

    protected function configure(): void
    {
        $this
            ->addOption('departements', null, InputOption::VALUE_REQUIRED, 'Export TSV de la table departement', 'var/legacy/departements.tsv')
            ->addOption('communes', null, InputOption::VALUE_REQUIRED, 'Export TSV des communes et de leurs circonscriptions', 'var/legacy/communes.tsv')
            ->addOption('adjacentes', null, InputOption::VALUE_REQUIRED, 'Export TSV des couples de communes limitrophes', 'var/legacy/communes_adjacentes.tsv')
            ->addOption('maires', null, InputOption::VALUE_REQUIRED, 'Export TSV des maires', 'var/legacy/maires.tsv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import de la géographie électorale');

        $departements = $this->importeDepartements((string) $input->getOption('departements'));
        $io->text(sprintf('%d départements.', $departements));

        $this->importeCommunes($io, (string) $input->getOption('communes'));
        $this->importeAdjacentes($io, (string) $input->getOption('adjacentes'));
        $this->importeMaires($io, (string) $input->getOption('maires'));

        $io->success(sprintf(
            '%d départements, %d communes (%d avec maire), %d rattachements et %d voisinages en base.',
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM departement'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM commune'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM commune WHERE maire_nom IS NOT NULL'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM commune_circonscription'),
            (int) $this->connection->fetchOne('SELECT COUNT(*) FROM commune_adjacente'),
        ));

        return Command::SUCCESS;
    }

    private function importeDepartements(string $fichier): int
    {
        // L'orthographe de l'Assemblée l'emporte sur celle du jeu `villes_fr`,
        // qui écrit « Côte-d'or », « Côtes-d'armor », « Val-d'oise » et
        // « Corse-du-sud ». Le code sert de pivot et passe en capitales pour
        // s'aligner sur `mandat` (« 2A » et non « 2a »).
        $nomsAssemblee = [];

        foreach ($this->connection->fetchAllKeyValue('SELECT DISTINCT departement_code, departement_nom FROM mandat WHERE departement_code IS NOT NULL') as $code => $nom) {
            $nomsAssemblee[strtoupper((string) $code)] = $nom;
        }

        $lot = [];

        foreach ($this->lignes($fichier) as $ligne) {
            [$code, $nom, $slug, $dans, $de, $region] = array_pad($ligne, 6, null);
            $code = strtoupper((string) $code);

            // Seule exception à la préférence pour l'Assemblée : elle écrit
            // « Polynésie Française », adjectif pourtant en minuscule — la
            // graphie du site (« Polynésie française », sa table departement)
            // est la bonne et c'est elle qui s'affiche partout.
            $lot[] = [$code, $code === '987' ? $nom : ($nomsAssemblee[$code] ?? $nom), $slug, $dans, $de, $region];
        }

        $this->upsert('departement', self::COLONNES_DEPARTEMENT, $lot, ['nom', 'slug', 'libelle_dans', 'libelle_de', 'region']);

        return \count($lot);
    }

    private function importeCommunes(SymfonyStyle $io, string $fichier): void
    {
        $idDepartements = $this->connection->fetchAllKeyValue('SELECT code, id FROM departement');

        /** @var array<string, list<int>> $circosParInsee */
        $circosParInsee = [];
        $lot = [];
        $communes = 0;
        $sansDepartement = 0;
        $nomsCorrompus = 0;

        $misAJour = ['nom', 'slug', 'population', 'population2012', 'code_postal', 'departement_id'];

        foreach ($this->lignes($fichier) as $ligne) {
            [$insee, $departement, $nom, $slug, $population, $circos, $codePostal, $population2012] = array_pad($ligne, 8, null);

            $idDepartement = $idDepartements[strtoupper((string) $departement)] ?? null;

            if ($idDepartement === null) {
                ++$sansDepartement;

                continue;
            }

            // Un nom ou un slug sans la moindre lettre est une corruption
            // connue de l'export : les communes nommées « Faux » ressortent en
            // « 0 » d'un dump passé par un tableur (FALSE en français). La
            // requête du docblock les répare à la source ; si la ligne arrive
            // quand même corrompue, on la refuse plutôt que d'écraser la ligne
            // saine déjà en base — et on le dit, pour que l'écart se voie.
            if (!preg_match('/\p{L}/u', (string) $nom) || !preg_match('/\p{L}/u', (string) $slug)) {
                ++$nomsCorrompus;

                continue;
            }

            $lot[] = [
                $insee,
                $nom,
                $slug,
                $population === null ? null : (int) $population,
                $population2012 === null ? null : (int) $population2012,
                // Le référentiel stocke le code postal en entier : « 1500 » pour
                // l'Ain, qu'il faut relire « 01500 ». Les 48 communes à codes
                // multiples les portent déjà sur cinq chiffres chacun, le
                // remplissage ne les touche donc pas.
                $codePostal === null ? null : str_pad($codePostal, 5, '0', \STR_PAD_LEFT),
                $idDepartement,
            ];
            ++$communes;

            if ($circos !== null && $circos !== '') {
                $circosParInsee[$insee] = array_map('intval', explode(',', $circos));
            }

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('commune', self::COLONNES_COMMUNE, $lot, $misAJour);
                $lot = [];
            }
        }

        $this->upsert('commune', self::COLONNES_COMMUNE, $lot, $misAJour);

        [$rattachements, $sansCommune] = $this->importeCirconscriptions($circosParInsee);

        $io->text(sprintf('%d communes, %d rattachements à une circonscription.', $communes, $rattachements));

        if ($sansDepartement > 0) {
            $io->text(sprintf('  %d communes écartées : département absent de l\'export.', $sansDepartement));
        }

        if ($nomsCorrompus > 0) {
            $io->warning(sprintf(
                '%d communes écartées : nom ou slug sans aucune lettre (« Faux » converti en « 0 » par un tableur ?). Régénérer l\'export avec la requête du docblock, qui répare depuis cities.nom_standard.',
                $nomsCorrompus,
            ));
        }

        if ($sansCommune > 0) {
            $io->text(sprintf('  %d rattachements écartés : commune non écrite.', $sansCommune));
        }
    }

    /**
     * @param array<string, list<int>> $circosParInsee
     *
     * @return array{0: int, 1: int} rattachements écrits, rattachements écartés
     */
    private function importeCirconscriptions(array $circosParInsee): array
    {
        $idCommunes = $this->idCommunesParInsee();

        $lot = [];
        $rattachements = 0;
        $sansCommune = 0;

        foreach ($circosParInsee as $insee => $circos) {
            $idCommune = $idCommunes[strtolower((string) $insee)] ?? null;

            if ($idCommune === null) {
                $sansCommune += \count($circos);

                continue;
            }

            foreach ($circos as $circonscription) {
                $lot[] = [$idCommune, $circonscription];
                ++$rattachements;
            }

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('commune_circonscription', self::COLONNES_CIRCONSCRIPTION, $lot, []);
                $lot = [];
            }
        }

        $this->upsert('commune_circonscription', self::COLONNES_CIRCONSCRIPTION, $lot, []);

        return [$rattachements, $sansCommune];
    }

    /**
     * Communes limitrophes. L'export ne retient que les voisines connues du
     * découpage électoral, et les deux sens du couple y figurent déjà.
     *
     * Reste 6 388 couples écartés sur 218 852, tous parce que la commune de
     * **départ** n'existe pas au découpage — l'export ne garantit que l'autre
     * bout, sa jointure ne portant que sur `adjacente`. Ils se répartissent
     * ainsi : 6 342 sur 1 559 codes que le référentiel INSEE de la production ne
     * connaît plus (des communes fusionnées, Maine-et-Loire, Calvados, Manche et
     * Orne en tête), et 46 sur neuf communes qu'il connaît mais qui n'ont pas de
     * circonscription — les six villages détruits de Verdun, sans habitant
     * depuis 1918, et trois communes rétablies après fusion.
     */
    private function importeAdjacentes(SymfonyStyle $io, string $fichier): void
    {
        $idCommunes = $this->idCommunesParInsee();

        $lot = [];
        $couples = 0;
        $inconnues = 0;

        foreach ($this->lignes($fichier) as [$insee, $adjacente]) {
            $commune = $idCommunes[strtolower((string) $insee)] ?? null;
            $voisine = $idCommunes[strtolower((string) $adjacente)] ?? null;

            if ($commune === null || $voisine === null) {
                ++$inconnues;

                continue;
            }

            $lot[] = [$commune, $voisine];
            ++$couples;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->upsert('commune_adjacente', self::COLONNES_ADJACENTE, $lot, []);
                $lot = [];
            }
        }

        $this->upsert('commune_adjacente', self::COLONNES_ADJACENTE, $lot, []);

        $io->text(sprintf('%d couples de communes limitrophes.', $couples));

        if ($inconnues > 0) {
            $io->text(sprintf('  %d couples écartés : commune absente du découpage électoral.', $inconnues));
        }
    }

    /**
     * Le maire de chaque commune.
     *
     * Écrit par `UPDATE` et non par upsert : la ligne de commune existe déjà,
     * et un `INSERT … ON DUPLICATE KEY` demanderait de reposer toutes ses
     * colonnes NOT NULL. Le fichier est facultatif — l'absence d'export ne
     * doit pas faire échouer un import de géographie.
     *
     * La correction manuelle du site (Berre-l'Étang, `SALVO` réécrit en
     * `Doriol`) n'est pas portée : son référentiel a depuis été remis à jour et
     * porte le bon nom, la substitution ne s'y déclenche plus.
     */
    private function importeMaires(SymfonyStyle $io, string $fichier): void
    {
        if (!is_file($fichier)) {
            $io->text('Aucun export de maires : colonnes laissées en l\'état.');

            return;
        }

        $idCommunes = $this->idCommunesParInsee();

        $maires = 0;
        $inconnues = 0;
        $lot = [];

        foreach ($this->lignes($fichier) as $ligne) {
            [$insee, $prenom, $nom, $civilite] = array_pad($ligne, 4, null);

            $idCommune = $idCommunes[strtolower((string) $insee)] ?? null;

            if ($idCommune === null) {
                ++$inconnues;

                continue;
            }

            if ($nom === null || $nom === '') {
                continue;
            }

            $lot[] = [$idCommune, $prenom, $nom, $civilite];
            ++$maires;

            if (\count($lot) >= self::TAILLE_LOT) {
                $this->ecrisMaires($lot);
                $lot = [];
            }
        }

        $this->ecrisMaires($lot);

        $io->text(sprintf('%d maires.', $maires));

        if ($inconnues > 0) {
            $io->text(sprintf('  %d maires écartés : commune absente du découpage électoral.', $inconnues));
        }
    }

    /**
     * @param list<array{0: int, 1: string|null, 2: string|null, 3: string|null}> $lot
     */
    private function ecrisMaires(array $lot): void
    {
        if ($lot === []) {
            return;
        }

        // Un CASE par colonne plutôt qu'un UPDATE par ligne : 35 000 allers et
        // retours coûteraient plus que tout le reste de l'import réuni.
        $ids = array_column($lot, 0);
        $marqueurs = implode(',', array_fill(0, \count($ids), '?'));

        $prenoms = $noms = $civilites = '';
        $valeurs = [];

        foreach ($lot as [$id, $prenom, $nom, $civilite]) {
            $prenoms .= ' WHEN ? THEN ?';
            $noms .= ' WHEN ? THEN ?';
            $civilites .= ' WHEN ? THEN ?';
            $valeurs[] = [$id, $prenom, $nom, $civilite];
        }

        $parametres = [];
        foreach ($valeurs as [$id, $prenom]) {
            $parametres[] = $id;
            $parametres[] = $prenom;
        }
        foreach ($valeurs as [$id, , $nom]) {
            $parametres[] = $id;
            $parametres[] = $nom;
        }
        foreach ($valeurs as [$id, , , $civilite]) {
            $parametres[] = $id;
            $parametres[] = $civilite;
        }

        $this->connection->executeStatement(
            'UPDATE commune SET
                maire_prenom = CASE id' . $prenoms . ' END,
                maire_nom = CASE id' . $noms . ' END,
                maire_civilite = CASE id' . $civilites . ' END
             WHERE id IN (' . $marqueurs . ')',
            array_merge($parametres, $ids),
        );
    }

    /**
     * Communes indexées par code INSEE, **en minuscules des deux côtés**.
     *
     * Les identifiants ne sont connus qu'une fois les communes écrites : d'où
     * cette carte tenue en mémoire — 36 000 entrées, sans commune mesure avec
     * les mandats.
     *
     * La normalisation, elle, n'est pas un confort. Les tables de la production
     * n'écrivent pas la Corse de la même façon : `circos`, d'où vient
     * `commune.code_insee`, écrit Ajaccio `2a004` quand `cities_adjacentes`
     * écrit `2A004`. Les collations de MariaDB ignorent la casse — l'export les
     * apparie donc sans difficulté et rien ne se voit en SQL. Un tableau PHP,
     * lui, distingue les deux : chercher `2A004` dans un index bâti sur `2a004`
     * ne trouvait rien, et l'import écartait **la totalité du voisinage corse**
     * (1 986 couples) sans un mot — les 360 communes de l'île perdaient leur
     * bandeau « Communes voisines » sur la fiche de ville comme sur la page de
     * résultats. C'est le piège décrit dans `CLAUDE.md` : ce qui se casse sur la
     * casse corse est toujours en dehors de la base.
     *
     * On normalise donc la clé de recherche, jamais la donnée : `code_insee`
     * garde l'orthographe que la production lui donne, et c'est un identifiant
     * qui est écrit dans `commune_adjacente`.
     *
     * @return array<string, int>
     */
    private function idCommunesParInsee(): array
    {
        $index = [];

        foreach ($this->connection->fetchAllKeyValue('SELECT code_insee, id FROM commune') as $insee => $id) {
            $index[strtolower((string) $insee)] = (int) $id;
        }

        return $index;
    }
}
