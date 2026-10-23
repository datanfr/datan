<?php

namespace App\Tricoteuses;

/**
 * Les jeux de données des Tricoteuses dont dépend le site.
 *
 * Seule la législature en cours est moissonnée quotidiennement : les dépôts des
 * législatures closes ne bougent plus (le dernier commit de Scrutins_XVI date
 * de 2024) et sont importés une fois pour toutes.
 */
final class Catalogue
{
    private const BASE = 'https://git.en-root.org/tricoteuses/data/';

    /** @var array<string, Depot>|null */
    private static ?array $depots = null;

    /**
     * @return array<string, Depot>
     */
    public static function tous(): array
    {
        return self::$depots ??= [
            'acteurs' => new Depot(
                'acteurs',
                self::BASE . 'assemblee-nettoye/AMO30_tous_acteurs_tous_mandats_tous_organes_historique_nettoye.git',
                'Acteurs, mandats et organes de toutes les législatures',
            ),
            'dossiers' => new Depot(
                'dossiers',
                self::BASE . 'assemblee-nettoye/Dossiers_Legislatifs_XVII_nettoye.git',
                'Dossiers législatifs de la 17e législature',
            ),
            // Les dossiers des législatures closes, pour les seuls initiateurs
            // et rapporteurs (`app:import:dossiers-acteurs`) : le dépôt de la
            // 17e ne porte que quelques dizaines de dossiers antérieurs, et sans
            // ceux-ci le bloc auteur d'une page de vote de 2017-2024 reste vide
            // là où datan.fr l'affiche. Figés, donc hors moisson nocturne.
            'dossiers-xv' => new Depot(
                'dossiers-xv',
                self::BASE . 'assemblee-nettoye/Dossiers_Legislatifs_XV_nettoye.git',
                'Dossiers législatifs de la 15e législature (2017-2022), close',
                quotidien: false,
            ),
            'dossiers-xvi' => new Depot(
                'dossiers-xvi',
                self::BASE . 'assemblee-nettoye/Dossiers_Legislatifs_XVI_nettoye.git',
                'Dossiers législatifs de la 16e législature (2022-2024), close',
                quotidien: false,
            ),
            'scrutins' => new Depot(
                'scrutins',
                self::BASE . 'assemblee-nettoye/Scrutins_XVII_nettoye.git',
                'Scrutins de la 17e législature, ventilations et votes nominatifs',
            ),
            // Les scrutins des législatures closes : dépôts figés (dernier
            // commit de Scrutins_XVI en 2024), importés une fois pour toutes
            // par `app:import:scrutins --depot=scrutins-xiv --tout` — d'où le
            // `quotidien: false` qui les tient hors de la moisson nocturne.
            'scrutins-xiv' => new Depot(
                'scrutins-xiv',
                self::BASE . 'assemblee-nettoye/Scrutins_XIV_nettoye.git',
                'Scrutins de la 14e législature (2012-2017), close',
                quotidien: false,
            ),
            'scrutins-xv' => new Depot(
                'scrutins-xv',
                self::BASE . 'assemblee-nettoye/Scrutins_XV_nettoye.git',
                'Scrutins de la 15e législature (2017-2022), close',
                quotidien: false,
            ),
            'scrutins-xvi' => new Depot(
                'scrutins-xvi',
                self::BASE . 'assemblee-nettoye/Scrutins_XVI_nettoye.git',
                'Scrutins de la 16e législature (2022-2024), close',
                quotidien: false,
            ),
            'amendements' => new Depot(
                'amendements',
                self::BASE . 'assemblee-nettoye/Amendements_XVII_nettoye.git',
                'Amendements de la 17e législature',
            ),
            'comptes-rendus' => new Depot(
                'comptes-rendus',
                self::BASE . 'assemblee-nettoye/Comptes_Rendus_Seances_XVII_nettoye.git',
                'Comptes rendus des séances publiques de la 17e législature',
            ),
            'photos' => new Depot(
                'photos',
                self::BASE . 'assemblee-photos/photos_deputes_17.git',
                'Photographies des députés de la 17e législature',
            ),
        ];
    }

    public static function get(string $nom): Depot
    {
        return self::tous()[$nom]
            ?? throw new \InvalidArgumentException(sprintf(
                'Dépôt « %s » inconnu. Disponibles : %s.',
                $nom,
                implode(', ', array_keys(self::tous())),
            ));
    }
}
