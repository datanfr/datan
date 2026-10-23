<?php

namespace App\Referencement;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * « L'hôte servi a-t-il le droit d'être indexé ? »
 *
 * La préproduction sert le site entier — mêmes pages, mêmes textes, mêmes
 * adresses relatives — sous un autre nom de domaine. Indexée, elle serait un
 * duplicata intégral de datan.fr, et les moteurs choisiraient eux-mêmes lequel
 * des deux ils gardent. Le référencement étant le fonds de commerce du site,
 * c'est un risque qu'on ne prend pas.
 *
 * Le tri se fait sur l'**hôte**, pas sur `APP_ENV` : la préproduction tourne en
 * `prod` — c'est tout son intérêt — et lui ressemble en tout point. Le nom de
 * domaine est le seul signe qui les distingue. Corollaire agréable : le jour de
 * la bascule, l'application servie sur `datan.fr` redevient indexable sans
 * qu'on touche à quoi que ce soit.
 *
 * Liste d'**exclusion** et non d'autorisation, à dessein : un hôte inconnu —
 * `www.`, une IP, une sonde de supervision, un essai local — reste indexable.
 * Se tromper dans ce sens-là fait indexer une copie ; se tromper dans l'autre
 * désindexe le vrai site, et c'est la seule des deux erreurs qui ne se rattrape
 * pas en une journée.
 */
final class Indexation
{
    /**
     * `Request::getHost()` rend l'hôte en minuscules et sans le port : écrire
     * les entrées de la même façon, sans quoi la comparaison stricte ne prend
     * jamais.
     */
    private const HOTES_NON_INDEXABLES = ['datan.remikel.fr'];

    public function __construct(private readonly RequestStack $requetes)
    {
    }

    public function estIndexable(): bool
    {
        $requete = $this->requetes->getMainRequest();

        // Hors requête HTTP (console, worker Messenger) : rien à désindexer.
        return null === $requete
            || !in_array($requete->getHost(), self::HOTES_NON_INDEXABLES, true);
    }
}
