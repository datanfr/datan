<?php

namespace App;

/**
 * Les dix-huit régions, du référentiel INSEE en vigueur depuis 2016.
 *
 * L'application d'origine tient une table `regions` que nous n'importons pas :
 * elle ne sert qu'à nommer la circonscription d'une candidature aux régionales
 * de 2021 — `candidature.district` en porte le code INSEE (« 32 ») —, sur la
 * page de l'élection comme dans les participations électorales d'une fiche
 * député, et le découpage n'a pas bougé depuis. Les deux collectivités à
 * statut particulier (Corse) et les cinq d'outre-mer y figurent, l'élection
 * s'y étant tenue.
 */
final class Region
{
    /** Libellé `election.libelle_abrege` des élections dont le district est une région. */
    public const ELECTION = 'Régionales';

    public const NOMS = [
        '1' => 'Guadeloupe',
        '2' => 'Martinique',
        '3' => 'Guyane',
        '4' => 'La Réunion',
        '6' => 'Mayotte',
        '11' => 'Île-de-France',
        '24' => 'Centre-Val de Loire',
        '27' => 'Bourgogne-Franche-Comté',
        '28' => 'Normandie',
        '32' => 'Hauts-de-France',
        '44' => 'Grand Est',
        '52' => 'Pays de la Loire',
        '53' => 'Bretagne',
        '75' => 'Nouvelle-Aquitaine',
        '76' => 'Occitanie',
        '84' => 'Auvergne-Rhône-Alpes',
        '93' => "Provence-Alpes-Côte d'Azur",
        '94' => 'Corse',
    ];
}
