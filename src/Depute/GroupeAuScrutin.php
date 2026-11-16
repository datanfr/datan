<?php

namespace App\Depute;

use App\Entity\FonctionGroupe;

/**
 * Groupe d'un député à la date d'un scrutin — celui dont sa loyauté se juge.
 *
 * `depute.groupe_id` ne porte que l'appartenance courante (cf. CLAUDE.md) :
 * confronter un vote de 2018 au groupe actuel laisse vide le groupe et la
 * loyauté de toute une législature passée, et fait passer pour rebelle qui a
 * changé de groupe depuis. Le legacy rattache chaque vote au mandat de groupe
 * en cours ce jour-là (`mandat_groupe` borné par `dateScrutin`, daily.php:2147) ;
 * on fait de même sur `fonction_groupe`.
 *
 * **Seuls comptent les rattachements d'appartenance** — membre, apparenté,
 * non-inscrit —, jamais une fonction exercée dans le groupe (présidence…) :
 * c'est le `codeQualite IN ("Membre", "Député non-inscrit", "Membre
 * apparenté")` du legacy. Une fonction se superpose à l'appartenance, elle ne
 * la remplace pas, et ses bornes ne disent rien du rattachement.
 *
 * **Et `nomin_principale` n'est PAS le bon filtre**, quoi qu'il en ait l'air.
 * L'Assemblée passe à 0 le mandat de simple membre d'un député tant qu'il
 * préside son groupe, et ce sur TOUTE la durée du mandat, rétroactivement ;
 * elle le repasse à 1 quand la présidence prend fin (Marine Le Pen, depuis le
 * 28/10/2026). Sébastien Chenu, membre du RN depuis le 19/07/2024 et président
 * depuis le 28/10/2026, n'avait donc plus de rattachement « principal » avant
 * cette date : 1 814 de ses votes ont perdu groupe et loyauté le soir de la
 * synchronisation qui l'a fait président (Naegelen 597, Marcangeli 289, Peu
 * 239). Les « onze doubles rattachements ouverts » de la 17e sont ces onze
 * présidents : un mandat de membre et un de président, DANS LE MÊME GROUPE.
 *
 * Restreints à l'appartenance, les rattachements d'un député se suivent sans
 * se chevaucher, sinon le jour d'un changement de groupe, où l'ancien finit et
 * le nouveau commence : le plus récent l'emporte, là où la jointure du legacy
 * dédoublait la ligne.
 */
final class GroupeAuScrutin
{
    /** Qualités qui font l'appartenance à un groupe, par opposition aux fonctions qui s'y exercent. */
    public const QUALITES_APPARTENANCE = [
        FonctionGroupe::QUALITE_MEMBRE,
        FonctionGroupe::QUALITE_APPARENTE,
        FonctionGroupe::QUALITE_NON_INSCRIT,
    ];

    /**
     * La même chose en condition SQL, sur `fonction_groupe` aliasée `fg`. Écrite
     * en constante pour servir aussi dans d'autres constantes de requête ; les
     * qualités sont des constantes internes, jamais une entrée utilisateur.
     */
    public const APPARTENANCE = "fg.code_qualite IN ('" . FonctionGroupe::QUALITE_MEMBRE . "', '"
        . FonctionGroupe::QUALITE_APPARENTE . "', '" . FonctionGroupe::QUALITE_NON_INSCRIT . "')";

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
                 WHERE fg.depute_id = $depute AND " . self::APPARTENANCE . "
                   AND fg.date_debut <= DATE($date)
                   AND (fg.date_fin IS NULL OR fg.date_fin >= DATE($date))
                 ORDER BY fg.date_debut DESC, fg.id DESC
                 LIMIT 1)";
    }

    /**
     * Même règle pour TOUS les votes d'un seul député, en jointure : ses
     * rattachements, rendus disjoints — chacun s'arrête la veille du suivant,
     * qui l'emporte donc le jour d'un changement de groupe. Sur les 8 200 votes
     * d'un député présent depuis 2012, la sous-requête scalaire, rejouée à
     * chaque ligne, coûtait 230 ms ; cette table de quelques lignes, 40 ms.
     *
     * Ce découpage par `LEAD` suppose des rattachements qui se succèdent : y
     * laisser une présidence, c'était laisser le hasard d'un ex aequo sur la
     * date de début trancher entre le mandat de membre ouvert et la présidence
     * close — Marine Le Pen perdait son groupe sur tout vote postérieur à la
     * fin de sa présidence. L'`id` départage les ex aequo restants, pour que la
     * même page rende toujours la même chose.
     *
     * Table dérivée à joindre `ON ` . {@see conditionPeriode()} ; `$depute` est
     * un paramètre nommé de la requête englobante (`:depute`).
     */
    public static function periodes(string $depute): string
    {
        return "(SELECT groupe_id, date_debut, date_fin,
                        LEAD(date_debut) OVER (ORDER BY date_debut, id) AS suivant
                 FROM fonction_groupe fg
                 WHERE fg.depute_id = $depute AND " . self::APPARTENANCE . ')';
    }

    /** Condition de jointure d'une table {@see periodes()} aliasée `$alias` sur la date `$date`. */
    public static function conditionPeriode(string $alias, string $date): string
    {
        return "$alias.date_debut <= DATE($date)
                AND ($alias.date_fin IS NULL OR $alias.date_fin >= DATE($date))
                AND ($alias.suivant IS NULL OR DATE($date) < $alias.suivant)";
    }
}
