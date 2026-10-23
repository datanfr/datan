<?php

namespace App\Command;

use App\Tricoteuses\Catalogue;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 * Socle des imports lisant les dépôts JSON des Tricoteuses.
 *
 * Deux régimes de fonctionnement : l'import complet, qui parcourt tout le
 * dépôt, et l'import incrémental, qui ne traite que les fichiers signalés par
 * {@see TricoteusesSyncCommand} comme ayant changé depuis la moisson
 * précédente. Le second est le régime quotidien — quelques dizaines de fichiers
 * au lieu de plusieurs milliers.
 */
abstract class ImportTricoteusesCommand extends Command
{
    protected const TAILLE_LOT = 500;

    public function __construct(
        protected readonly Connection $connection,
        private readonly string $racineTricoteuses,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('depot', null, InputOption::VALUE_REQUIRED, 'Nom du dépôt des Tricoteuses, ou chemin vers un clone')
            ->addOption('tout', null, InputOption::VALUE_NONE, 'Réimporte tout le dépôt, sans tenir compte du delta de la dernière moisson');
    }

    /**
     * Résout l'option --depot : un nom du catalogue, un chemin, ou la valeur
     * par défaut propre à la commande.
     */
    protected function chemin(InputInterface $input): string
    {
        $depot = (string) ($input->getOption('depot') ?: $this->depotParDefaut());

        $chemin = is_dir($depot) ? $depot : Catalogue::get($depot)->chemin($this->racineTricoteuses);

        if (!is_dir($chemin)) {
            throw new \RuntimeException(sprintf(
                'Dépôt absent de « %s ». Lancez d\'abord : php bin/console app:tricoteuses:sync --depot=%s',
                $chemin,
                $depot,
            ));
        }

        return $chemin;
    }

    /** Nom du dépôt utilisé quand --depot n'est pas fourni. */
    abstract protected function depotParDefaut(): string;

    /**
     * Les fichiers à traiter sous `$chemin/$sousDossier`.
     *
     * En régime incrémental, seuls ceux que la moisson a signalés. Le fichier
     * de delta liste des chemins relatifs à la racine du dépôt, y compris des
     * suppressions déjà filtrées par la moisson : on vérifie tout de même leur
     * existence, un dépôt pouvant être remanié entre deux exécutions.
     *
     * Les dépôts n'ont pas tous la même forme. Certains rangent leurs fichiers
     * dans un sous-dossier (`acteurs/acteurs/`), d'autres à la racine — à plat
     * pour les photos, un dossier par texte législatif pour les amendements.
     * Un `$sousDossier` vide parcourt donc le dépôt entier, et `$extension`
     * couvre les dépôts qui ne publient pas du JSON.
     *
     * @return iterable<string> chemins absolus
     */
    protected function fichiers(string $chemin, string $sousDossier, bool $tout, string $extension = 'json'): iterable
    {
        $prefixe = $sousDossier === '' ? '' : rtrim($sousDossier, '/') . '/';
        $suffixe = '.' . $extension;
        $delta = $chemin . '/' . TricoteusesSyncCommand::FICHIER_DELTA;

        if (!$tout && is_file($delta)) {
            foreach (file($delta, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES) as $relatif) {
                if (!str_starts_with($relatif, $prefixe) || !str_ends_with($relatif, $suffixe)) {
                    continue;
                }
                $absolu = $chemin . '/' . $relatif;
                if (is_file($absolu)) {
                    yield $absolu;
                }
            }

            return;
        }

        $racine = rtrim($chemin . '/' . $prefixe, '/');
        if (!is_dir($racine)) {
            return;
        }

        // Sans ce filtre, l'import complet descendrait dans `.git` — coûteux sur
        // un dépôt de 120 000 fichiers, et hors sujet : le dépôt de travail est
        // l'arbre de travail, pas son historique.
        $iterateur = new \RecursiveIteratorIterator(
            new \RecursiveCallbackFilterIterator(
                new \RecursiveDirectoryIterator($racine, \FilesystemIterator::SKIP_DOTS),
                static fn (\SplFileInfo $fichier) => !str_starts_with($fichier->getFilename(), '.'),
            ),
        );

        foreach ($iterateur as $fichier) {
            if ($fichier->getExtension() === $extension) {
                yield $fichier->getPathname();
            }
        }
    }

    protected function incremental(string $chemin, bool $tout): bool
    {
        return !$tout && is_file($chemin . '/' . TricoteusesSyncCommand::FICHIER_DELTA);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function lisJson(string $fichier): ?array
    {
        $contenu = file_get_contents($fichier);
        if ($contenu === false) {
            return null;
        }

        $donnees = json_decode($contenu, true);

        return \is_array($donnees) ? $donnees : null;
    }

    /**
     * Upsert par lot : un seul INSERT ... ON DUPLICATE KEY UPDATE par paquet.
     * À ces volumes, passer par l'ORM coûterait plusieurs ordres de grandeur.
     *
     * Deux régimes de mise à jour, à choisir colonne par colonne. `$misAJour`
     * réécrit sans condition : c'est ce qu'on veut d'une donnée que l'open data
     * publie toujours, ou d'un `updated_at`. `$misAJourSiRenseigne` ne réécrit
     * que si la moisson apporte une valeur — l'open data publie irrégulièrement,
     * et une colonne absente d'une moisson ne rend pas fausse celle qu'on a
     * déjà. C'est aussi le seul régime admissible pour une colonne qu'une autre
     * source alimente (rédaction, scraping) : sans lui, l'import quotidien
     * l'effacerait chaque nuit.
     *
     * @param list<string>      $colonnes
     * @param list<list<mixed>> $lignes
     * @param list<string>      $misAJour           colonnes réécrites en cas de doublon
     * @param list<string>      $misAJourSiRenseigne colonnes réécrites seulement si la nouvelle valeur n'est pas NULL
     */
    protected function upsert(string $table, array $colonnes, array $lignes, array $misAJour, array $misAJourSiRenseigne = []): void
    {
        if ($lignes === []) {
            return;
        }

        $tuple = '(' . implode(', ', array_fill(0, \count($colonnes), '?')) . ')';
        $affectations = array_merge(
            array_map(static fn (string $c) => sprintf('%1$s = VALUES(%1$s)', $c), $misAJour),
            array_map(static fn (string $c) => sprintf('%1$s = COALESCE(VALUES(%1$s), %1$s)', $c), $misAJourSiRenseigne),
        );

        if ($affectations === []) {
            // Aucune colonne à reprendre : l'insertion doit tout de même être
            // idempotente, sans quoi une réexécution échouerait sur la clé.
            $affectations = [sprintf('%1$s = %1$s', $colonnes[0])];
        }

        $this->connection->executeStatement(
            sprintf(
                'INSERT INTO %s (%s) VALUES %s ON DUPLICATE KEY UPDATE %s',
                $table,
                implode(', ', $colonnes),
                implode(', ', array_fill(0, \count($lignes), $tuple)),
                implode(', ', $affectations),
            ),
            array_merge(...$lignes),
        );
    }

    /** Date ISO de l'open data (« 2026-07-17T00:00:00+02:00 ») en date SQL. */
    protected function date(?string $valeur): ?string
    {
        if ($valeur === null || $valeur === '') {
            return null;
        }

        return substr($valeur, 0, 10);
    }

    protected function entier(mixed $valeur): ?int
    {
        return is_numeric($valeur) ? (int) $valeur : null;
    }
}
