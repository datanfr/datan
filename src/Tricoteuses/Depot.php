<?php

namespace App\Tricoteuses;

/**
 * Un jeu de données publié par les Tricoteuses.
 *
 * Les Tricoteuses ne servent pas les données par une API HTTP mais par des
 * dépôts Git de fichiers JSON, remoissonnés quotidiennement depuis l'open data
 * de l'Assemblée nationale puis nettoyés (variantes « _nettoye » : structures
 * normalisées, tableaux toujours des tableaux). Git est donc l'interface : il
 * donne le transfert incrémental et, surtout, la liste exacte des fichiers
 * modifiés depuis la dernière moisson.
 *
 * @see https://git.en-root.org/tricoteuses/data
 */
final class Depot
{
    /**
     * `$quotidien` dit si le dépôt entre dans la moisson sans option de
     * `app:tricoteuses:sync` — donc dans `app:sync:quotidien`. Les dépôts des
     * législatures closes ne bougent plus : ils restent clonables à la demande
     * (`--depot=scrutins-xiv`) sans être re-tirés chaque nuit pour rien.
     */
    public function __construct(
        public readonly string $nom,
        public readonly string $url,
        public readonly string $description,
        public readonly bool $quotidien = true,
    ) {
    }

    public function chemin(string $racine): string
    {
        return $racine . '/' . $this->nom;
    }
}
