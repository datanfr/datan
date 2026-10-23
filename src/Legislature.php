<?php

namespace App;

/**
 * Repères de législature, portés des helpers de l'application CodeIgniter
 * d'origine (legislature_current(), legislature_all(), dissolution()).
 */
final class Legislature
{
    /** Législature en cours — `legislature_current()` dans l'application d'origine. */
    public const COURANTE = 17;

    /** Législature la plus ancienne publiée : en deçà, l'application d'origine renvoie un 404. */
    public const PREMIERE = 14;

    /**
     * Jour d'ouverture d'une législature achevée — la date de prise de fonction
     * que porte le mandat de tous les députés élus aux élections générales.
     *
     * Table reprise de `Deputes_model::get_deputes_gender()`, qui s'en sert pour
     * compter la composition de l'Assemblée *à l'ouverture* plutôt que
     * l'ensemble de ceux qui y ont siégé. La législature en cours n'y figure
     * pas : elle se borne par les mandats encore ouverts.
     */
    private const OUVERTURE = [
        12 => '2002-06-19',
        13 => '2007-06-20',
        14 => '2012-06-20',
        15 => '2017-06-21',
        16 => '2022-06-22',
    ];

    /**
     * Législatures proposées dans les blocs « Historique », de la plus récente
     * à la plus ancienne.
     *
     * @return list<int>
     */
    public static function publiees(): array
    {
        return range(self::COURANTE, self::PREMIERE);
    }

    /**
     * Jour d'ouverture d'une législature achevée, ou null pour celle en cours.
     */
    public static function ouverture(int $legislature): ?string
    {
        return self::OUVERTURE[$legislature] ?? null;
    }
}
