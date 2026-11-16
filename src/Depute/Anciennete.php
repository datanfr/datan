<?php

namespace App\Depute;

/**
 * Temps passé sur les bancs de l'Assemblée, partagé entre la fiche d'un député
 * et les jeux de données publiés (`app:opendata:publier`), qui l'écrivent dans
 * les mêmes mots : « 9 ans », « 7 mois ».
 */
final class Anciennete
{
    /**
     * Jours d'un mandat, à sommer par député sur la table `mandat` sans alias :
     * la prise de fonction borne le début (un suppléant entre en cours de
     * législature), un mandat encore ouvert court jusqu'à aujourd'hui.
     */
    public const JOURS_SQL = 'DATEDIFF(COALESCE(date_fin, CURDATE()), COALESCE(date_prise_fonction, date_debut))';

    /**
     * Ancienneté en toutes lettres, comme le legacy (`daily.php:3994`) : en années
     * dès un an, sinon en mois, sinon en jours.
     */
    public static function enLettres(int $jours): string
    {
        $annees = (int) round($jours / 365);
        if ($annees >= 1) {
            return $annees . ($annees > 1 ? ' ans' : ' an');
        }

        $mois = (int) round($jours / 30);

        return $mois !== 0 ? $mois . ' mois' : $jours . ' jours';
    }
}
