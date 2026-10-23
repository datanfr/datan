<?php

namespace App;

/**
 * Familles socio-professionnelles de l'INSEE et leur poids dans la population
 * française, portés de la table `famsocpro` de l'application d'origine.
 *
 * L'ordre des familles est celui de cette table : c'est l'ordre d'affichage du
 * tableau et des barres du graphique de la page « origine sociale ».
 */
final class FamilleSocioPro
{
    public const AGRICULTEURS = 'Agriculteurs exploitants';
    public const ARTISANS = 'Artisans, commerçants et chefs d\'entreprise';
    public const CADRES = 'Cadres et professions intellectuelles supérieures';
    public const INTERMEDIAIRES = 'Professions intermédiaires';
    public const EMPLOYES = 'Employés';
    public const OUVRIERS = 'Ouvriers';
    public const RETRAITES = 'Retraités';
    public const INACTIFS = 'Personnes n’ayant jamais travaillé (ex étudiants)';

    /**
     * Ce que le site écrit pour les députés qu'aucune famille ne range.
     *
     * Ce n'est pas une neuvième famille de l'INSEE : elle n'a pas de poids dans
     * la population et ne compte pas dans l'indice de Rose. Mais elle pèse
     * 43 députés sur 577, et le tableau croisé de la page la montre — sans
     * elle, les colonnes ne totalisent pas 100 % et le lecteur cherche en vain
     * où sont passés les manquants.
     */
    public const SANS_PROFESSION = 'Sans profession déclarée';

    /**
     * Part de chaque famille dans la population française, en pourcentage.
     *
     * @return array<string, float>
     */
    public static function population(): array
    {
        return [
            self::AGRICULTEURS => 0.7,
            self::ARTISANS => 3.5,
            self::CADRES => 10.6,
            self::INTERMEDIAIRES => 13.7,
            self::EMPLOYES => 14.2,
            self::OUVRIERS => 10.9,
            self::RETRAITES => 33.4,
            self::INACTIFS => 12.7,
        ];
    }

    /**
     * Ramène un libellé de l'open data de l'Assemblée à l'une des huit familles,
     * ou à `null` quand la profession du député ne permet pas de le classer.
     *
     * L'open data emploie plusieurs graphies pour une même famille — « Artisans,
     * commerçants, chefs d'entreprises » et « Artisans, commerçants et chefs
     * d'entreprise », « Professions Intermédiaires » et « Professions
     * intermédiaires ». L'application d'origine rapproche ces libellés de sa
     * table de référence par égalité stricte : les graphies divergentes n'y
     * trouvent pas de correspondance et les députés concernés disparaissent
     * silencieusement de ses statistiques. On les rattache ici explicitement.
     *
     * « Sans profession déclarée » n'est pas une famille mais une absence de
     * donnée : ces députés sont comptés dans l'effectif, sans famille.
     */
    public static function normalise(?string $libelle): ?string
    {
        $libelle = trim((string) $libelle);

        if ($libelle === '') {
            return null;
        }

        return match ($libelle) {
            'Artisans, commerçants, chefs d\'entreprises' => self::ARTISANS,
            'Professions Intermédiaires' => self::INTERMEDIAIRES,
            'Autres personnes sans activité professionnelle' => self::INACTIFS,
            'Sans profession déclarée' => null,
            default => \array_key_exists($libelle, self::population()) ? $libelle : null,
        };
    }
}
