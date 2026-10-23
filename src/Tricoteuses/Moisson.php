<?php

namespace App\Tricoteuses;

/**
 * Résultat de la synchronisation d'un dépôt : ce qui a changé depuis la
 * dernière exécution.
 */
final class Moisson
{
    /**
     * @param list<string>|null $fichiersModifies Chemins relatifs au dépôt, ou
     *        null lorsque le delta n'a pas pu être établi (premier clonage,
     *        historique tronqué) : il faut alors tout réimporter.
     */
    public function __construct(
        public readonly Depot $depot,
        public readonly string $chemin,
        public readonly ?array $fichiersModifies,
        public readonly ?string $commitAvant,
        public readonly string $commitApres,
    ) {
    }

    public function complete(): bool
    {
        return $this->fichiersModifies === null;
    }

    public function inchange(): bool
    {
        return $this->fichiersModifies === [];
    }

    public function resume(): string
    {
        if ($this->complete()) {
            return 'import complet requis';
        }

        $n = \count($this->fichiersModifies);

        return $n === 0 ? 'aucun changement' : sprintf('%d fichier%s modifié%s', $n, $n > 1 ? 's' : '', $n > 1 ? 's' : '');
    }
}
