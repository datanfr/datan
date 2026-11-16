<?php

namespace App\Ia\Tag;

/**
 * Ramène un mot-clé proposé par le modèle à sa clé de vocabulaire : casse,
 * accents, ponctuation et pluriel simple effacés. « Retraites », « retraite »
 * et « RETRAITE » donnent `retraite` ; « Méga-bassines », `mega-bassine`.
 *
 * C'est le premier filtre, gratuit : tout ce qu'il rapproche n'a pas à passer
 * par le modèle ({@see RapprochementTags}). Il ne fait que ce qui est sûr — un
 * synonyme ou un sigle (« IVG » / « interruption volontaire de grossesse »)
 * reste l'affaire du modèle.
 *
 * Le pluriel est traité mot à mot, par règles et non par dictionnaire :
 * l'important n'est pas d'obtenir le singulier correct mais la **même clé**
 * pour les deux nombres. « travaux » donne `traval`, que « travail » ne
 * rejoint pas : c'est le modèle qui rattrapera, une fois, puis l'alias.
 */
final class NormaliseurTag
{
    /** Longueur des colonnes `tag.libelle` et `tag.slug`. */
    public const LONGUEUR_MAX = 100;

    /**
     * Mots invariables que la règle du « s » final abîmerait en les confondant
     * avec autre chose, ou qui finissent par « s » au singulier.
     */
    private const INVARIABLES = [
        'bus', 'gaz', 'pays', 'prix', 'fois', 'temps', 'corps', 'droits', 'tabac',
        'mers', 'jus', 'virus', 'sans', 'vers', 'sous', 'dans', 'plus', 'moins', 'apres',
    ];

    /**
     * Libellé tel qu'on le range : espaces resserrés, rogné, borné. Null s'il
     * ne reste rien.
     */
    public static function libelle(mixed $valeur): ?string
    {
        if (!\is_string($valeur)) {
            return null;
        }

        $libelle = trim((string) preg_replace('/\s+/u', ' ', strip_tags($valeur)));
        $libelle = trim($libelle, " \t\n\r\0\x0B.,;:!?\"'«»");

        if ($libelle === '' || mb_strlen($libelle) > self::LONGUEUR_MAX) {
            return null;
        }

        return $libelle;
    }

    /** Clé du vocabulaire ; chaîne vide si le libellé ne porte aucune lettre ni chiffre. */
    public static function slug(string $libelle): string
    {
        $ascii = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $libelle);
        if ($ascii === false) {
            $ascii = mb_strtolower($libelle);
        }

        // Apostrophes, traits d'union et espaces deviennent des séparateurs :
        // « l'eau » et « l eau » se rejoignent, « méga-bassines » et
        // « méga bassines » aussi.
        $mots = preg_split('/[^a-z0-9]+/', $ascii, -1, \PREG_SPLIT_NO_EMPTY) ?: [];

        return implode('-', array_map(self::singulier(...), $mots));
    }

    private static function singulier(string $mot): string
    {
        if (\in_array($mot, self::INVARIABLES, true) || ctype_digit($mot)) {
            return $mot;
        }

        $longueur = \strlen($mot);

        // réseaux → reseau, eaux → eau
        if ($longueur >= 4 && str_ends_with($mot, 'eaux')) {
            return substr($mot, 0, -1);
        }
        // journaux → journal, hôpitaux → hopital ; « taux » (4 lettres) reste.
        if ($longueur > 5 && str_ends_with($mot, 'aux')) {
            return substr($mot, 0, -3) . 'al';
        }
        // retraites → retraite ; « ss » final (« stress ») n'est pas un pluriel,
        // ni un mot de trois lettres (« bus » est déjà écarté, « pas », « dos »…).
        if ($longueur > 3 && str_ends_with($mot, 's') && !str_ends_with($mot, 'ss')) {
            return substr($mot, 0, -1);
        }

        return $mot;
    }
}
