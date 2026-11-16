<?php

namespace App\Election;

/**
 * Libellé et couleur d'une nuance de liste aux municipales 2026
 * (`Elections_model::get_nuances_edited()` de l'application d'origine).
 *
 * Le ministère publie un code (`LUG`) et un libellé long (« Liste d'union de la
 * gauche ») ; le site affiche un libellé court de sa rédaction — « Union de la
 * gauche », « PCF », « MODEM » — et une pastille de couleur. C'est une table
 * éditoriale, reprise telle quelle : un code absent garde son code pour
 * libellé et prend le gris des divers.
 */
final class NuanceMunicipale
{
    public const GRIS = '#888888';

    /** @var array<string, array{0: string, 1: string}> code => [libellé, couleur] */
    private const NUANCES = [
        'LCOM' => ['PCF', '#e4001f'],
        'LDIV' => ['Divers', '#888888'],
        'LDSV' => ['Droite souverainiste', '#003189'],
        'LDVC' => ['Divers centre', '#ffcc00'],
        'LDVD' => ['Divers droite', '#0a3ec2'],
        'LDVG' => ['Divers gauche', '#ff8c69'],
        'LECO' => ['Divers écologiste', '#2e9e44'],
        'LEXD' => ['Extrême droite', '#302a6e'],
        'LEXG' => ['Extrême gauche', '#8B0000'],
        'LFI' => ['LFI', '#cc0000'],
        'LHOR' => ['Horizons', '#00a0c8'],
        'LLR' => ['Les Républicains', '#0033a0'],
        'LMDM' => ['MODEM', '#f07800'],
        'LREC' => ['Reconquête !', '#0d2f6e'],
        'LREG' => ['Régionalistes', '#66bb6a'],
        'LREN' => ['Renaissance', '#e8b31a'],
        'LRN' => ['Rassemblement national', '#0d378a'],
        'LSOC' => ['Parti socialiste', '#e5007d'],
        'LUC' => ['Union du centre', '#ffcc00'],
        'LUD' => ['Union de la droite', '#0a3ec2'],
        'LUDI' => ['UDI', '#00adef'],
        'LUDR' => ['UDR', '#1a1a6e'],
        'LUG' => ['Union de la gauche', '#cc0000'],
        'LUXD' => ['Union d\'extrême droite', '#302a6e'],
    ];

    public static function libelle(?string $code): string
    {
        return self::NUANCES[(string) $code][0] ?? (string) $code;
    }

    public static function couleur(?string $code): string
    {
        return self::NUANCES[(string) $code][1] ?? self::GRIS;
    }
}
