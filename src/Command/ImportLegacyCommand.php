<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;

/**
 * Socle des imports qui lisent un export de la base de production, par
 * opposition à {@see ImportTricoteusesCommand} qui lit un dépôt Git de JSON.
 *
 * Certaines données ne sont dans aucun dépôt des Tricoteuses : la géographie
 * électorale, les résultats des élections, les parrainages. Elles ne vivent que
 * dans la base de l'application d'origine, qui n'est pas joignable depuis ici —
 * le port 3307 de l'hôte sert la base de développement, la production est dans
 * le conteneur `datan-db`. On passe donc par des fichiers TSV, et **chaque
 * commande porte dans son docblock la requête `docker exec` qui régénère les
 * siens** : sans cela, un import n'est plus rejouable dès que le fichier est
 * perdu.
 */
abstract class ImportLegacyCommand extends Command
{
    protected const TAILLE_LOT = 1000;

    public function __construct(protected readonly Connection $connection)
    {
        parent::__construct();
    }

    /**
     * Lignes utiles d'un export `mariadb -B` : l'en-tête est écarté, `NULL` rend
     * un null, et les champs ne sont surtout pas rognés — l'espace final de
     * « des » et de « du » dans `departement.libelle_de` fait partie de la
     * donnée.
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

            while (($ligne = fgets($flux)) !== false) {
                $ligne = rtrim($ligne, "\r\n");

                if ($ligne === '') {
                    continue;
                }

                yield array_map(
                    static fn (string $champ) => $champ === 'NULL' || $champ === '\N' ? null : $champ,
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
