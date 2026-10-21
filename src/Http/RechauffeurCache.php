<?php

namespace App\Http;

use App\Twig\DatanExtension;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Vide et réchauffe le cache HTTP des pages publiques.
 *
 * Une page hors cache coûte 0,3 à 0,8 s de rendu (jusqu'à 1,7 s à froid), la
 * même en cache ~30 ms. Sans réchauffage, c'est le premier visiteur de chaque
 * page qui paie le calcul ; avec, c'est cette classe, une fois.
 *
 * **Le contrat d'invalidation.** Les données ne changent qu'à deux moments :
 * la synchronisation quotidienne, et la publication d'un contenu éditorial
 * (décryptage, explication de vote, article). Chacun purge tout le magasin puis
 * le réchauffe. Entre les deux rien ne bouge, d'où une durée de cache d'un jour
 * ({@see self::DUREE_PAGES}) : elle ne sert que de filet si une purge manque.
 * Purger l'ensemble plutôt que les seules adresses touchées est voulu — un
 * décryptage paraît sur la page du vote, mais aussi sur l'accueil, la fiche de
 * chaque député, celle de chaque groupe, les listes… Énumérer ces pages serait
 * un second contrat à tenir, qu'on finirait par oublier.
 *
 * **Les requêtes passent dans le processus, sans HTTP** : par le même
 * {@see CacheAvecSession} que `public/index.php`, qui écrit dans le même
 * magasin `var/cache/prod/http_cache`. Pas de serveur web occupé, pas de
 * réseau, pas de mot de passe de préproduction à fournir.
 */
final class RechauffeurCache
{
    /**
     * Durée de vie d'une page publique dans le cache partagé. Un jour : la
     * fraîcheur est assurée par les purges, pas par l'expiration.
     */
    public const DUREE_PAGES = 86400;

    /**
     * Plans du site dont les pages sont réchauffées. Le sitemap annonce déjà
     * exactement ce qui répond 200 : il fait la liste, sans second critère à
     * maintenir. Écartés à dessein : les communes et leurs résultats
     * électoraux (~35 000 adresses rarement visitées), les députés et groupes
     * des législatures passées (~3 400), les 19 000 pages de scrutin. Ces pages
     * se mettent en cache à leur première visite, comme avant.
     */
    private const PLANS = [
        'sitemap_structure',
        'sitemap_deputes',
        'sitemap_groupes',
        'sitemap_partis',
        'sitemap_departements',
        'sitemap_elections',
        'sitemap_blog_categories',
        'sitemap_blog_articles',
    ];

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly Connection $connection,
        private readonly UrlGeneratorInterface $routeur,
        private readonly MessageBusInterface $bus,
        private readonly DatanExtension $datan,
        #[Autowire('%env(DEFAULT_URI)%')]
        private readonly string $adressePublique,
    ) {
    }

    /**
     * À appeler après l'écriture d'un contenu publié : la page change
     * aussitôt, le réchauffage suit en tâche de fond (consommateur Messenger),
     * le temps qu'il faut sans faire attendre le rédacteur.
     */
    public function apresPublication(): void
    {
        // En debug, `public/index.php` ne passe pas par le cache HTTP : rien à
        // purger, et personne pour consommer la demande.
        if ($this->kernel->isDebug()) {
            return;
        }

        $this->purger();
        $this->bus->dispatch(new RechauffageDemande(microtime(true)));
    }

    /**
     * Vide le magasin d'un coup en le renommant, ce qui est atomique et
     * instantané quel que soit le nombre de pages. Supprimer les fichiers un à
     * un ferait attendre la requête qui purge, et laisserait les visiteurs lire
     * un magasin à moitié vidé. Le répertoire écarté est effacé au
     * réchauffage suivant.
     */
    public function purger(): void
    {
        $magasin = $this->magasin();

        if (!is_dir($magasin)) {
            return;
        }

        // Sous Windows, un fichier encore ouvert par une requête empêche le
        // renommage : on efface alors en place.
        if (!@rename($magasin, $magasin . '.purge-' . bin2hex(random_bytes(4)))) {
            (new Filesystem())->remove($magasin);
        }
    }

    /**
     * @param callable(string, Response): void|null $suivi appelé après chaque page
     *
     * @return array{pages: int, echecs: array<string, int>}
     */
    public function rechauffer(?callable $suivi = null): array
    {
        // Deux réchauffages simultanés (le cron et une publication) referaient
        // deux fois le même travail : le second attend la fin du premier.
        $verrou = fopen($this->kernel->getProjectDir() . '/var/rechauffage.lock', 'c');
        flock($verrou, LOCK_EX);

        try {
            touch($this->kernel->getProjectDir() . '/var/rechauffage.debut');
            (new Filesystem())->remove(glob($this->magasin() . '.purge-*') ?: []);

            // allow_reload : la requête `no-cache` force le rendu et remplace
            // l'entrée existante. Sans lui, réchauffer un magasin non purgé
            // relirait simplement les pages en place.
            $cache = new CacheAvecSession($this->kernel, null, null, ['allow_reload' => true]);
            $echecs = [];
            $pages = 0;

            foreach ($this->adresses($cache) as $url) {
                $reponse = $this->servir($cache, $url);
                ++$pages;

                if ($reponse->getStatusCode() !== Response::HTTP_OK) {
                    $echecs[$url] = $reponse->getStatusCode();
                }

                if ($suivi !== null) {
                    $suivi($url, $reponse);
                }
            }

            return ['pages' => $pages, 'echecs' => $echecs];
        } finally {
            flock($verrou, LOCK_UN);
            fclose($verrou);
        }
    }

    /**
     * Un rédacteur qui enregistre cinq décryptages d'affilée pose cinq
     * demandes. Celles émises avant le début du dernier réchauffage sont déjà
     * satisfaites : elles ne relancent rien.
     */
    #[AsMessageHandler]
    public function traiterDemande(RechauffageDemande $demande): void
    {
        $debut = @filemtime($this->kernel->getProjectDir() . '/var/rechauffage.debut');

        if ($debut !== false && $debut >= $demande->demandeeLe) {
            return;
        }

        $this->rechauffer();
    }

    /**
     * Les adresses des plans, puis les pages des scrutins décryptés : ce sont
     * elles que la rédaction vient de toucher, et la raison d'être du site.
     *
     * @return iterable<string>
     */
    private function adresses(CacheAvecSession $cache): iterable
    {
        foreach (self::PLANS as $plan) {
            $plan = $this->servir($cache, $this->absolue($this->routeur->generate($plan)));

            // Les plans sont générés depuis la requête qui les demande : leurs
            // adresses portent déjà l'hôte public.
            preg_match_all('#<loc>([^<]+)</loc>#', (string) $plan->getContent(), $correspondances);

            foreach ($correspondances[1] as $url) {
                yield htmlspecialchars_decode($url, ENT_XML1);
            }
        }

        // Lu d'un bloc et non en flux : chaque page servie entre deux lignes
        // interroge la même connexion.
        foreach ($this->connection->fetchAllNumeric(
            "SELECT legislature, vote_numero FROM decryptage WHERE state = 'published' ORDER BY legislature DESC, vote_numero DESC",
        ) as [$legislature, $numero]) {
            yield $this->absolue($this->datan->lienVote((int) $legislature, (int) $numero));
        }
    }

    private function servir(CacheAvecSession $cache, string $url): Response
    {
        $requete = Request::create($url, server: ['HTTP_CACHE_CONTROL' => 'no-cache']);
        $reponse = $cache->handle($requete);
        $cache->terminate($requete, $reponse);

        return $reponse;
    }

    /**
     * La clé d'une page dans le magasin est son URL complète, hôte et schéma
     * compris. Réchauffer `http://localhost/deputes` remplirait des entrées
     * que nul visiteur de `https://datan.fr/deputes` ne lirait jamais : d'où
     * `DEFAULT_URI`, qui doit être l'adresse publique telle que PHP la voit.
     */
    private function absolue(string $chemin): string
    {
        return rtrim($this->adressePublique, '/') . $chemin;
    }

    private function magasin(): string
    {
        return $this->kernel->getCacheDir() . '/http_cache';
    }
}
