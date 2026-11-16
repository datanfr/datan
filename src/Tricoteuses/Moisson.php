<?php

namespace App\Tricoteuses;

/**
 * Résultat de la synchronisation d'un dépôt : ce qui reste à importer.
 *
 * Le delta ne se mesure pas depuis la moisson précédente mais depuis le
 * dernier import **réussi** ({@see Moissonneur::acquitte()}) : une moisson
 * dont l'import n'est pas allé au bout ne fait pas disparaître ses fichiers,
 * la suivante les représente.
 */
final class Moisson
{
    /**
     * @param list<string>|null $fichiersModifies Chemins relatifs au dépôt, ou
     *        null lorsque le delta n'a pas pu être établi (premier clonage,
     *        historique tronqué, dépôt déménagé) : il faut alors tout réimporter.
     * @param string|null       $commitAcquitte   Dernier commit importé avec
     *        succès, base du delta ; null tant qu'aucune chaîne n'a abouti
     *        depuis l'introduction de l'acquittement.
     */
    public function __construct(
        public readonly Depot $depot,
        public readonly string $chemin,
        public readonly ?array $fichiersModifies,
        public readonly ?string $commitAvant,
        public readonly string $commitApres,
        public readonly ?string $commitAcquitte = null,
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

    /**
     * Vrai quand le delta porte encore des moissons antérieures à celle-ci :
     * le dernier import réussi est plus ancien que la moisson précédente.
     * C'est le signe, dans le journal, qu'une chaîne a échoué entre-temps.
     */
    public function enRetard(): bool
    {
        return $this->commitAcquitte !== null && $this->commitAcquitte !== $this->commitAvant;
    }

    public function resume(): string
    {
        if ($this->complete()) {
            return 'import complet requis';
        }

        $n = \count($this->fichiersModifies);
        $texte = $n === 0 ? 'aucun changement' : sprintf('%d fichier%s à importer', $n, $n > 1 ? 's' : '');

        return $this->enRetard()
            ? sprintf('%s, en attente depuis %s', $texte, substr((string) $this->commitAcquitte, 0, 8))
            : $texte;
    }
}
