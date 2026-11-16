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
 * (décryptage, explication de vote, article). Chacun purge le magasin des
 * pages vivantes puis le réchauffe. Entre les deux rien ne bouge, d'où une
 * durée de cache d'un jour ({@see self::DUREE_PAGES}) : elle ne sert que de
 * filet si une purge manque. Purger l'ensemble plutôt que les seules adresses
 * touchées est voulu — un décryptage paraît sur la page du vote, mais aussi
 * sur l'accueil, la fiche de chaque député, celle de chaque groupe, les
 * listes… Énumérer ces pages serait un second contrat à tenir, qu'on finirait
 * par oublier.
 *
 * **Les pages figées n'entrent pas dans ce cycle.** Elles ne décrivent que du
 * passé, n'expirent pas ({@see self::DUREE_FIGEE}) et vivent dans un second
 * magasin ({@see MagasinDouble}) que la purge ordinaire laisse en place. Il
 * n'est vidé que lorsque ses sources bougent : `purger(tout: true)`,
 * qu'appellent les imports lancés à la main et les écritures de la rédaction
 * qui touchent au passé, et la synchronisation quand {@see EmpreinteFigee}
 * constate un changement.
 *
 * **Les requêtes passent dans le processus, sans HTTP** : par le même
 * {@see CacheAvecSession} que `public/index.php`, qui écrit dans les mêmes
 * magasins sous `var/cache/prod`. Pas de serveur web occupé, pas de
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
     * Durée de vie d'une page figée : un an, la façon dont HTTP dit « sans
     * expiration ». Poser cette durée sur une réponse, c'est la ranger dans le
     * magasin figé ({@see MagasinDouble::estFigee()}) — donc affirmer que tout
     * ce qu'elle montre est dans le périmètre d'{@see EmpreinteFigee}, que
     * rien n'y dépend du jour du rendu, et que toute écriture de la rédaction
     * qui la touche passe par `apresPublication(tout: true)`.
     */
    public const DUREE_FIGEE = 31536000;

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
        private readonly EmpreinteFigee $empreinte,
        #[Autowire('%env(DEFAULT_URI)%')]
        private readonly string $adressePublique,
    ) {
    }

    /**
     * À appeler après l'écriture d'un contenu publié : la page change
     * aussitôt, le réchauffage suit en tâche de fond (consommateur Messenger),
     * le temps qu'il faut sans faire attendre le rédacteur.
     *
     * @param bool $tout vrai quand l'écriture touche aussi une page figée — un
     *   décryptage ou une explication sur un scrutin d'une législature close
     */
    public function apresPublication(bool $tout = false): void
    {
        // En debug, `public/index.php` ne passe pas par le cache HTTP : rien à
        // purger, et personne pour consommer la demande.
        if ($this->kernel->isDebug()) {
            return;
        }

        $this->purger($tout);
        $this->bus->dispatch(new RechauffageDemande(microtime(true)));
    }

    /**
     * Vide le magasin des pages vivantes — et celui des pages figées quand le
     * passé vient d'être réécrit (`tout`).
     *
     * D'un coup, en renommant le dossier, ce qui est atomique et instantané
     * quel que soit le nombre de pages. Supprimer les fichiers un à un ferait
     * attendre la requête qui purge, et laisserait les visiteurs lire un
     * magasin à moitié vidé. Le répertoire écarté est effacé au réchauffage
     * suivant.
     */
    public function purger(bool $tout = false): void
    {
        $this->ecarter(MagasinDouble::VIVANT);

        if ($tout) {
            $this->ecarter(MagasinDouble::FIGE);
            // Le prochain réchauffage notera de quel état des sources le
            // magasin repart ; d'ici là, la synchronisation le viderait encore.
            $this->empreinte->oublier($this->fichierEmpreinte());
        }
    }

    /**
     * Purge de fin de synchronisation : les pages vivantes toujours, les
     * figées seulement si leurs sources ont bougé depuis la dernière fois
     * qu'on a regardé — un député qui change de groupe, l'Assemblée qui
     * réécrit le passé, un import lancé à la main qui a oublié de le dire.
     *
     * @return bool vrai si le magasin figé a été vidé lui aussi
     */
    public function purgerApresSynchronisation(): bool
    {
        $this->ecarter(MagasinDouble::VIVANT);

        if (!$this->empreinte->aChange($this->fichierEmpreinte())) {
            return false;
        }

        $this->ecarter(MagasinDouble::FIGE);

        return true;
    }

    /**
     * Retire du cache quelques adresses seulement, dans les deux magasins.
     *
     * L'exception au contrat « on purge tout » : elle vaut pour une écriture
     * dont on sait exactement où elle paraît, et qui se répète — relire cent
     * résumés d'amendements à la suite viderait cent fois le cache du site.
     * Les pages retirées se refont à leur prochaine visite.
     *
     * @param list<string> $chemins chemins absolus depuis la racine du site
     */
    public function purgerAdresses(array $chemins): void
    {
        if ($this->kernel->isDebug()) {
            return;
        }

        $magasin = MagasinDouble::sous($this->kernel->getCacheDir());

        foreach ($chemins as $chemin) {
            $magasin->purge($this->absolue($chemin));
        }
    }

    private function ecarter(string $nom): void
    {
        $magasin = $this->kernel->getCacheDir() . '/' . $nom;

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
            (new Filesystem())->remove(glob($this->kernel->getCacheDir() . '/http_cache*.purge-*') ?: []);

            // Après un déploiement ou une purge complète, le magasin figé est
            // vide : on note de quel état de ses sources il repart. Sans
            // cela, la synchronisation suivante le viderait faute de point de
            // comparaison.
            $this->empreinte->consignerSiAbsente($this->fichierEmpreinte());

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

    /**
     * À côté des magasins, donc propre à la version déployée comme eux.
     */
    private function fichierEmpreinte(): string
    {
        return $this->kernel->getCacheDir() . '/' . MagasinDouble::FIGE . '.empreinte';
    }
}
