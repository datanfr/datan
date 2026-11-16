<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Socle des imports qui lisent un export de la base de production, par
 * opposition à {@see ImportTricoteusesCommand} qui lit un dépôt Git de JSON.
 *
 * Certaines données ne sont dans aucun dépôt des Tricoteuses : la géographie
 * électorale, les résultats des élections, les parrainages, et tout le contenu
 * éditorial. Elles ne vivent que dans la base de l'application d'origine, qu'on
 * ne lit pas directement — ni d'ici, ni depuis l'application déployée. On passe
 * par des fichiers TSV.
 *
 * **Les requêtes d'export vivent dans `bin/recuperation/legacy/<nom>.sql`, et
 * nulle part ailleurs.** `bin/recuperation/exporter-legacy.sh` les joue toutes
 * contre la base du legacy et écrit un `<nom>.tsv` pour chacune — le nom que
 * porte l'option de la commande. Le docblock d'une commande renvoie à son
 * fichier ; il ne recopie plus la requête, qui finirait par diverger.
 *
 * Chaque commande consigne son bilan ({@see consigner()}) : lignes lues,
 * écrites, écartées et pourquoi. `app:recuperation:legacy`, qui les enchaîne
 * toutes, le relit et vérifie que le compte tombe juste à l'unité.
 */
abstract class ImportLegacyCommand extends Command
{
    protected const TAILLE_LOT = 1000;

    /** @var array<string, int> lignes de données lues, par fichier */
    private array $lues = [];

    /** @var list<array{etape: string, lues: int, ecrites: int, ecartees: array<string, int>}> */
    private array $bilans = [];

    public function __construct(protected readonly Connection $connection)
    {
        parent::__construct();
    }

    /** Les compteurs repartent de zéro à chaque exécution : la commande est un service partagé. */
    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->lues = [];
        $this->bilans = [];

        return parent::run($input, $output);
    }

    /**
     * Bilans de la dernière exécution, une entrée par fichier lu.
     *
     * @return list<array{etape: string, lues: int, ecrites: int, ecartees: array<string, int>}>
     */
    public function bilans(): array
    {
        return $this->bilans;
    }

    /**
     * Consigne et affiche le bilan d'une étape. `lues` doit valoir `ecrites` +
     * la somme des `ecartees` : sinon une règle d'écart manque au compte, et
     * une règle fausse ressemble en tout point à une source incomplète.
     *
     * `$lues` vaut par défaut le nombre de lignes de données que {@see lignes()}
     * a lues dans `$fichier`, en-tête exclu — la commande n'a pas à les compter.
     *
     * @param array<string, int> $ecartees raison → nombre
     */
    protected function consigner(SymfonyStyle $io, string $etape, string $fichier, int $ecrites, array $ecartees = [], ?int $lues = null): void
    {
        $ecartees = array_filter($ecartees);
        $lues ??= $this->lues[$fichier] ?? 0;

        $this->bilans[] = ['etape' => $etape, 'lues' => $lues, 'ecrites' => $ecrites, 'ecartees' => $ecartees];

        $detail = $ecartees === [] ? '' : ' — écartées : ' . implode(', ', array_map(
            static fn (string $raison, int $n) => sprintf('%d %s', $n, $raison),
            array_keys($ecartees),
            $ecartees,
        ));

        $io->text(sprintf('<info>%s</info> : %d lue(s), %d écrite(s)%s.', $etape, $lues, $ecrites, $detail));

        if ($lues !== $ecrites + array_sum($ecartees)) {
            $io->warning(sprintf('%s : %d ligne(s) ni écrite(s) ni écartée(s).', $etape, $lues - $ecrites - array_sum($ecartees)));
        }
    }

    /**
     * Lignes utiles d'un export `mariadb -B` : l'en-tête est écarté, `NULL` rend
     * un null, et les champs ne sont surtout pas rognés — l'espace final de
     * « des » et de « du » dans `departement.libelle_de` fait partie de la
     * donnée.
     *
     * Le mode `-B` échappe chaque champ (`\n`, `\t`, `\0` et l'antislash
     * lui-même), sans quoi un saut de ligne dans un corps d'article couperait
     * l'enregistrement. On le défait ici, pour tous les champs et une seule
     * fois : un HTML gardé avec des `\n` littéraux s'afficherait tel quel, et un
     * antislash d'origine — dans le JSON de `newsletter.depute` par exemple —
     * sortirait doublé.
     *
     * La lecture est en flux : les résultats des européennes font 2,5 millions
     * de lignes, que rien n'oblige à tenir en mémoire.
     *
     * @return iterable<list<string|null>>
     */
    protected function lignes(string $fichier): iterable
    {
        $flux = fopen($fichier, 'r');

        if ($flux === false) {
            throw new \RuntimeException(sprintf('Export introuvable : « %s ».', $fichier));
        }

        try {
            fgets($flux);
            $this->lues[$fichier] = 0;

            while (($ligne = fgets($flux)) !== false) {
                $ligne = rtrim($ligne, "\r\n");

                if ($ligne === '') {
                    continue;
                }

                ++$this->lues[$fichier];
                $echappee = str_contains($ligne, '\\');

                yield array_map(
                    static fn (string $champ) => match (true) {
                        $champ === 'NULL' || $champ === '\N' => null,
                        $echappee => strtr($champ, ['\n' => "\n", '\t' => "\t", '\0' => "\0", '\\\\' => '\\']),
                        default => $champ,
                    },
                    explode("\t", $ligne),
                );
            }
        } finally {
            fclose($flux);
        }
    }

    /**
     * Upsert par lot, dans l'esprit de {@see ImportTricoteusesCommand::upsert()}.
     *
     * `$misAJourSiRenseigne` traduit en `COALESCE(VALUES(c), c)` : la colonne
     * n'est réécrite que si l'export la renseigne. C'est le régime à retenir
     * chaque fois qu'une source peut publier une colonne vide sans que
     * l'information soit fausse pour autant.
     *
     * @param list<string>      $colonnes
     * @param list<list<mixed>> $lignes
     * @param list<string>      $misAJour
     * @param list<string>      $misAJourSiRenseigne
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
        ) ?: [sprintf('%1$s = %1$s', $colonnes[0])];

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

    /** Un champ vide d'un export vaut absence de donnée, pas donnée vide. */
    protected function texte(?string $valeur): ?string
    {
        return $valeur === null || trim($valeur) === '' ? null : $valeur;
    }

    protected function entier(?string $valeur): ?int
    {
        $valeur = $this->texte($valeur);

        return $valeur === null ? null : (int) $valeur;
    }

    /**
     * Drapeau `tinyint(1)` de la base d'origine, où l'absence de valeur a un
     * sens — une candidature dont on ne sait pas encore si elle est élue.
     *
     * Rendu en entier et non en booléen : le pilote mysqli lie un `false` PHP
     * comme chaîne vide, que MariaDB refuse dans une colonne entière. Le `null`,
     * lui, passe et reste un `null`.
     */
    protected function drapeau(?string $valeur): ?int
    {
        $valeur = $this->texte($valeur);

        return $valeur === null ? null : (int) ($valeur === '1');
    }
}
