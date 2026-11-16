<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;

/**
 * Deux magasins derrière le reverse proxy, parce que deux sortes de pages.
 *
 * - **Les pages vivantes** changent avec la synchronisation du matin ou une
 *   publication : elles vivent un jour ({@see RechauffeurCache::DUREE_PAGES})
 *   et leur magasin est vidé à chacun de ces deux moments.
 * - **Les pages figées** décrivent du passé ou de la géographie — les
 *   résultats d'une élection tenue, un groupe d'une législature close, les
 *   députés d'une commune. Elles n'expirent pas
 *   ({@see RechauffeurCache::DUREE_FIGEE}) et leur magasin survit aux purges
 *   ordinaires : il n'est vidé que si leurs sources ont réellement bougé
 *   ({@see EmpreinteFigee}). Avant cette séparation, la purge quotidienne jetait
 *   chaque matin des dizaines de milliers de pages identiques à la veille,
 *   que le premier visiteur — un robot d'indexation, le plus souvent — payait
 *   une à une au prix d'un rendu complet.
 *
 * **C'est la réponse qui dit sa sorte**, par sa durée : un contrôleur qui pose
 * `DUREE_FIGEE` range sa page dans le magasin figé, sans autre déclaration. La
 * requête seule ne le dit pas — la même route sert un député en exercice et un
 * ancien —, d'où une lecture dans les deux magasins, le vivant d'abord.
 *
 * Les deux vivent sous `var/cache/<env>` : chaque version déployée a le sien
 * et repart donc à vide. C'est voulu — une page figée cite les feuilles de
 * style de la version qui l'a rendue, qu'un déploiement ultérieur efface.
 */
final class MagasinDouble implements StoreInterface
{
    public const VIVANT = 'http_cache';

    public const FIGE = 'http_cache_fige';

    public function __construct(
        private readonly StoreInterface $vivant,
        private readonly StoreInterface $fige,
    ) {
    }

    public static function sous(string $racine): self
    {
        return new self(
            new Store($racine . '/' . self::VIVANT),
            new Store($racine . '/' . self::FIGE),
        );
    }

    public static function estFigee(Response $reponse): bool
    {
        return (int) $reponse->headers->getCacheControlDirective('s-maxage') >= RechauffeurCache::DUREE_FIGEE;
    }

    /**
     * Le vivant d'abord : c'est là que sont les pages les plus demandées.
     */
    public function lookup(Request $request): ?Response
    {
        return $this->vivant->lookup($request) ?? $this->fige->lookup($request);
    }

    public function write(Request $request, Response $response): string
    {
        // Une adresse à paramètres ne se fige pas : il en existe une infinité
        // (`?a=1`, `?a=2`…) pour la même page, et un magasin qu'on ne vide
        // presque jamais les garderait toutes.
        if (self::estFigee($response) && $request->getQueryString() === null) {
            return $this->fige->write($request, $response);
        }

        // Une page peut changer de sorte — un ancien député retrouve un siège.
        // L'entrée figée qu'elle aurait laissée ressortirait, valable un an,
        // dès la purge suivante du magasin vivant : on l'efface ici.
        $this->fige->purge($request->getUri());

        return $this->vivant->write($request, $response);
    }

    public function invalidate(Request $request): void
    {
        $this->vivant->invalidate($request);
        $this->fige->invalidate($request);
    }

    /**
     * Un seul jeu de verrous : il protège le rendu d'une adresse, quelle que
     * soit l'étagère où la réponse ira ensuite.
     */
    public function lock(Request $request): bool|string
    {
        return $this->vivant->lock($request);
    }

    public function unlock(Request $request): bool
    {
        return $this->vivant->unlock($request);
    }

    public function isLocked(Request $request): bool
    {
        return $this->vivant->isLocked($request);
    }

    public function purge(string $url): bool
    {
        $vivant = $this->vivant->purge($url);
        $fige = $this->fige->purge($url);

        return $vivant || $fige;
    }

    public function cleanup(): void
    {
        $this->vivant->cleanup();
        $this->fige->cleanup();
    }
}
