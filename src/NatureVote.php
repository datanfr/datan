<?php

namespace App;

/**
 * Nature de l'objet soumis au scrutin, déduite de son libellé.
 *
 * L'Assemblée nationale ne publie pas cette information : elle est déduite du
 * libellé du scrutin (« l'ensemble du projet de loi… », « l'amendement n° 12
 * de M. X… »). C'est le portage à l'identique du classement opéré par
 * `updateVoteInfo()` dans le script `daily.php` de l'application d'origine, qui
 * alimentait la colonne `votes_info.voteType`.
 *
 * L'ordre des tests est significatif et ne doit pas être réarrangé : un
 * sous-amendement contient « amendement », un vote sur l'ensemble d'un texte
 * peut mentionner un article.
 */
final class NatureVote
{
    /** Vote sur l'ensemble d'un texte — le vote qui adopte ou rejette la loi. */
    public const FINALE = 'final';

    public const AMENDEMENT = 'amendement';
    public const SOUS_AMENDEMENT = 'sous-amendement';

    /**
     * Position maximale du marqueur « ensemble d » pour valoir vote final.
     *
     * Couvre « l'ensemble du… », « sur l'ensemble du… » et leurs variantes avec
     * espace ou apostrophe typographique. Au-delà, le marqueur appartient au
     * titre du texte, pas à l'objet du vote.
     */
    private const TETE_LIBELLE = 12;

    public static function depuisLibelle(?string $libelle): ?string
    {
        if ($libelle === null || $libelle === '') {
            return null;
        }

        return match (true) {
            self::voteFinal($libelle) => self::FINALE,
            str_starts_with($libelle, "l'articl"), str_starts_with($libelle, " l'artic") => 'article',
            str_contains($libelle, 'sous-amendement'),
            str_contains($libelle, 'sous-amendment') => self::SOUS_AMENDEMENT,
            str_contains($libelle, 'amendement') => self::AMENDEMENT,
            str_contains($libelle, 'a motion de rejet prealable'),
            str_contains($libelle, 'a motion de rejet préalable') => 'motion de rejet préalable',
            str_contains($libelle, 'a motion de renvoi en commi') => 'motion de renvoi en commission',
            str_contains($libelle, 'a motion de censure') => 'motion de censure',
            str_contains($libelle, 'motion référendaire') => 'motion référendaire',
            str_contains($libelle, 'a declaration de politique generale') => 'declaration de politique generale',
            str_contains($libelle, 'es crédits de la mission'),
            str_contains($libelle, 'es credits de') => 'crédits de mission',
            str_contains($libelle, 'a déclaration du Gouvernement') => 'déclaration du gouvernement',
            str_contains($libelle, 'partie du projet de loi de finances') => 'partie du projet de loi de finances',
            str_contains($libelle, 'demande de constitution de commission speciale'),
            str_contains($libelle, 'demande de constitution de la commission speciale') => 'demande de constitution de commission speciale',
            str_contains($libelle, 'demande de suspension de séance') => 'demande de suspension de séance',
            str_contains($libelle, "motion d'ajournement") => "motion d'ajournement",
            str_contains($libelle, 'conclusions de rejet de la commission') => 'conclusions de rejet de la commission',
            str_contains($libelle, 'projet de loi constitutionnelle') => 'projet de loi constitutionnelle',
            str_contains($libelle, 'demande') => 'demande',
            default => null,
        };
    }

    /**
     * Un vote final se reconnaît à « l'ensemble du texte » **en tête** du
     * libellé, là où l'objet du scrutin est énoncé.
     *
     * C'est une divergence assumée avec l'application d'origine, qui cherche
     * « ensemble d » n'importe où (`daily.php:1747`). Cinq scrutins en portent
     * un dans le titre du texte lui-même et se retrouvent classés votes finaux
     * à tort — « l'amendement n° 5 de M. de Lépinau … une menace sanitaire pour
     * l'ensemble du vignoble français » (17e, n° 915), ou une motion de rejet
     * en 14e législature. Ils faussent le taux de soutien au gouvernement, qui
     * ne compte que les votes finaux.
     */
    private static function voteFinal(string $libelle): bool
    {
        $position = mb_strpos($libelle, 'ensemble d');

        return $position !== false && $position <= self::TETE_LIBELLE;
    }
}
