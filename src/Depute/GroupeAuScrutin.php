<?php

namespace App\Depute;

/**
 * Groupe d'un député à la date d'un scrutin — celui dont sa loyauté se juge.
 *
 * `depute.groupe_id` ne porte que l'appartenance courante (cf. CLAUDE.md) :
 * confronter un vote de 2018 au groupe actuel laisse vide le groupe et la
 * loyauté de toute une législature passée, et fait passer pour rebelle qui a
 * changé de groupe depuis. Le legacy rattache chaque vote au mandat de groupe
 * en cours ce jour-là (`mandat_groupe` borné par `dateScrutin`, daily.php:2047) ;
 * on fait de même sur `fonction_groupe`.
 *
 * `nomin_principale` écarte les doubles rattachements (onze députés de la 17e en
 * ont deux ouverts). Le jour d'un changement de groupe, l'ancien rattachement
 * finit et le nouveau commence : le plus récent l'emporte, là où la jointure
 * du legacy dédoublait la ligne. La qualité n'est pas filtrée : un président
 * de groupe (qualité « Président ») reste rattaché à son groupe.
 */
final class GroupeAuScrutin
{
    /**
     * Sous-requête scalaire rendant l'id du groupe. `$depute` et `$date` sont des
     * expressions SQL de la requête englobante (`v.depute_id`, `s.date_scrutin`),
     * jamais une entrée utilisateur. La date d'un scrutin est un horodatage, et
     * les bornes d'un rattachement des dates : sans `DATE()`, le dernier jour
     * d'un rattachement tomberait hors de lui dès midi passé.
     */
    public static function sousRequete(string $depute, string $date): string
    {
        return "(SELECT fg.groupe_id FROM fonction_groupe fg
                 WHERE fg.depute_id = $depute AND fg.nomin_principale = 1
                   AND fg.date_debut <= DATE($date)
                   AND (fg.date_fin IS NULL OR fg.date_fin >= DATE($date))
                 ORDER BY fg.date_debut DESC
                 LIMIT 1)";
    }

    /**
     * Même règle pour TOUS les votes d'un seul député, en jointure : ses
     * rattachements, rendus disjoints — chacun s'arrête la veille du suivant,
     * qui l'emporte donc le jour d'un changement de groupe. Sur les 8 200 votes
     * d'un député présent depuis 2012, la sous-requête scalaire, rejouée à
     * chaque ligne, coûtait 230 ms ; cette table de quelques lignes, 40 ms.
     *
     * Table dérivée à joindre `ON ` . {@see conditionPeriode()} ; `$depute` est
     * un paramètre nommé de la requête englobante (`:depute`).
     */
    public static function periodes(string $depute): string
    {
        return "(SELECT groupe_id, date_debut, date_fin,
                        LEAD(date_debut) OVER (ORDER BY date_debut) AS suivant
                 FROM fonction_groupe
                 WHERE depute_id = $depute AND nomin_principale = 1)";
    }

    /** Condition de jointure d'une table {@see periodes()} aliasée `$alias` sur la date `$date`. */
    public static function conditionPeriode(string $alias, string $date): string
    {
        return "$alias.date_debut <= DATE($date)
                AND ($alias.date_fin IS NULL OR $alias.date_fin >= DATE($date))
                AND ($alias.suivant IS NULL OR DATE($date) < $alias.suivant)";
    }
}
