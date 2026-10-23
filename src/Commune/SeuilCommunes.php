<?php

namespace App\Commune;

/**
 * Collectivités dont les listes de communes échappent au seuil de population.
 *
 * Les blocs « Communes les plus peuplées » et les pastilles des pages
 * d'élections ne retiennent une commune qu'au-delà d'un seuil (4 000 habitants
 * pour les fiches de ville, 500 pour les résultats). Or cinq collectivités
 * d'outre-mer n'ont **aucune** population au référentiel — `commune.population`
 * est NULL sur toutes leurs communes : Polynésie française (48),
 * Nouvelle-Calédonie (33), Saint-Pierre-et-Miquelon (2), Saint-Barthélemy et
 * Saint-Martin (2), Wallis-et-Futuna (1). Le site titrait donc au-dessus d'une
 * liste vide. Depuis le 6 septembre 2026 (`City_model::get_communes_by_dpt()`,
 * commit 79e5795f), il les exempte du seuil et plafonne leur liste à vingt
 * communes — `min($limit, 20)`, ou 20 quand aucune limite n'était demandée.
 *
 * La règle vaut partout où le site appelle cette méthode : bloc des communes
 * des pages de département et de ville, pastilles et pied des pages de
 * résultats, et les deux plans de site qui en reprennent le critère.
 *
 * Clé = `departement.slug`, comme chez lui : c'est l'adresse, le contrat
 * stable — ni le code, ni le nom, qui ont déjà changé de graphie.
 *
 * Tri à l'intérieur du plafond : nos requêtes rangent `population DESC, nom`,
 * ce qui, sur des populations toutes nulles, retient les vingt premières par
 * nom. Le site, lui, ne trie que sur la population et prend donc vingt lignes
 * dans l'ordre physique de sa table — un accident de stockage, qu'on ne
 * reproduit pas.
 */
final class SeuilCommunes
{
    /** @var list<string> */
    public const SANS_SEUIL = [
        'saint-pierre-et-miquelon-975',
        'saint-barthelemy-et-saint-martin',
        'wallis-et-futuna-986',
        'polynesie-francaise-987',
        'nouvelle-caledonie-988',
    ];

    /** Nombre maximal de communes listées pour une collectivité exemptée. */
    public const PLAFOND = 20;

    public static function exempte(string $slug): bool
    {
        return \in_array($slug, self::SANS_SEUIL, true);
    }

    /**
     * Limite effective d'une liste : celle demandée, rabattue au plafond pour
     * une collectivité exemptée ; null vaut « sans limite ».
     */
    public static function limite(string $slug, ?int $limite): ?int
    {
        if (!self::exempte($slug)) {
            return $limite;
        }

        return $limite === null ? self::PLAFOND : min($limite, self::PLAFOND);
    }

    /**
     * Fragment SQL du seuil, à poser dans un `WHERE` : `(:exempte = 1 OR
     * population > :seuil)`. Les deux paramètres sont à lier par
     * {@see self::parametres()} — des entiers, jamais un booléen, que le pilote
     * mysqli lierait en chaîne vide.
     */
    public static function condition(string $colonne = 'population'): string
    {
        return '(:exempte = 1 OR ' . $colonne . ' > :seuil)';
    }

    /**
     * @return array{exempte: int, seuil: int}
     */
    public static function parametres(string $slug, int $seuil): array
    {
        return ['exempte' => self::exempte($slug) ? 1 : 0, 'seuil' => $seuil];
    }
}
