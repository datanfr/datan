<?php

namespace App\Controller;

use App\Http\RechauffeurCache;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Blog de la rédaction (contrôleur Posts de l'application d'origine, vues
 * views/posts/). Les articles sont des données de production récupérées par
 * `app:import:articles` ; les images vivent sous `assets/imgs/posts/`,
 * récupérées du serveur de production — elles ne sont pas dans le dépôt du
 * legacy, qui les fabrique à l'upload.
 */
class BlogController extends AbstractController
{
    /** Le blog ne bouge qu'à la publication d'un article, qui purge le cache. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /** Longueur de l'extrait des cartes, en mots (word_limiter du legacy). */
    private const MOTS_EXTRAIT = 25;

    /** Longueur de la description de la page d'un article, en caractères. */
    private const CARACTERES_DESCRIPTION = 300;

    /**
     * Sous-titres et descriptions des rubriques. L'origine ne les stocke pas en
     * base : ils sont écrits en dur dans sa bibliothèque `libraries/Blog.php`,
     * la table `categories` n'ayant que le nom et le slug. Même choix ici — une
     * rubrique se crée dans le code, pas dans un écran d'administration.
     */
    private const RUBRIQUES = [
        'actualite-politique' => [
            'sous_titre' => "Nos analyses sur l'actualité de l'Assemblée nationale",
            'description' => "Découvrez nos analyses approfondies sur l'actualité des députés et de l'Assemblée nationale.",
        ],
        'datan' => [
            'sous_titre' => 'Les nouvelles du projet Datan',
            'description' => 'Vous voulez tout savoir sur le projet Datan ? Découvrez nos dernières nouvelles dans ce blog.',
        ],
        'rapports' => [
            'sous_titre' => "Nos études sur l'Assemblée nationale et les députés",
            'description' => 'Retrouvez nos études et analyses sur le travail parlementaire.',
        ],
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/blog', name: 'blog_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->liste(null);
    }

    // Déclarée avant blog_article : « categorie » entrerait sinon dans son
    // motif {rubrique} — même collision que le legacy règle par l'ordre de
    // routes.php (blog/categorie avant blog/(:any)/(:any)).
    #[Route('/blog/categorie/{rubrique}', name: 'blog_categorie', requirements: ['rubrique' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function categorie(string $rubrique): Response
    {
        return $this->liste($rubrique);
    }

    #[Route('/blog/{rubrique}/{slug}', name: 'blog_article', requirements: ['rubrique' => '[a-z0-9\-]+', 'slug' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function article(string $rubrique, string $slug): Response
    {
        $article = $this->connection->fetchAssociative(
            "SELECT a.id, a.titre, a.slug, a.corps, a.image_nom, a.cree_le, a.modifie_le,
                    c.nom AS rubrique_nom, c.slug AS rubrique_slug
             FROM article a
             JOIN categorie_article c ON c.id = a.categorie_id
             WHERE a.slug = :slug AND c.slug = :rubrique AND a.etat = 'published'
             LIMIT 1",
            ['slug' => $slug, 'rubrique' => $rubrique],
        );

        if ($article === false) {
            throw $this->createNotFoundException('Article introuvable.');
        }

        // À défaut de nom d'image, l'origine retombe sur `img_post_<id>`.
        $image = $article['image_nom'] !== null && $article['image_nom'] !== ''
            ? $article['image_nom']
            : 'img_post_' . $article['id'];

        $urlArticle = $this->generateUrl('blog_article', ['rubrique' => $rubrique, 'slug' => $slug]);
        $urlRubrique = $this->generateUrl('blog_categorie', ['rubrique' => $rubrique]);
        $urlImage = 'assets/imgs/posts/' . $image . '.png';

        $response = $this->render('blog/article.html.twig', [
            'article' => $article,
            'image' => $image,
            'date_publication' => $this->dateFrancaise($article['cree_le']),
            'description' => $this->description((string) $article['corps']),
            'url_image' => $urlImage,
            'ogp' => [
                'titre' => $article['titre'] . ' | Datan',
                'type' => 'website',
                'image' => $this->urlAbsolue($urlImage),
                // L'origine émet des attributs de dimensions vides sur les
                // articles (NULL dans Meta_model) : reproduits vides plutôt que
                // remplacés par les 1200×630 du repli générique, qui mentiraient.
                'image_largeur' => '',
                'image_hauteur' => '',
            ],
            // Seule page du fil d'Ariane où TOUT est lié, l'article compris :
            // l'origine n'y marque aucun maillon actif.
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Blog', 'url' => $this->generateUrl('blog_index')],
                ['nom' => $article['rubrique_nom'], 'url' => $urlRubrique],
                ['nom' => $article['titre'], 'url' => $urlArticle, 'actif' => false],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    private function liste(?string $rubrique): Response
    {
        $detailRubrique = $rubrique !== null ? (self::RUBRIQUES[$rubrique] ?? null) : null;

        if ($rubrique !== null && $detailRubrique === null) {
            throw $this->createNotFoundException('Rubrique inconnue.');
        }

        $articles = $this->connection->fetchAllAssociative(
            "SELECT a.id, a.titre, a.slug, a.corps, a.image_nom, a.cree_le,
                    c.nom AS rubrique_nom, c.slug AS rubrique_slug
             FROM article a
             JOIN categorie_article c ON c.id = a.categorie_id
             WHERE a.etat = 'published'" . ($rubrique !== null ? ' AND c.slug = :rubrique' : '') . '
             ORDER BY a.cree_le DESC',
            $rubrique !== null ? ['rubrique' => $rubrique] : [],
        );

        // Une rubrique sans article publié répond 404, comme l'origine : une
        // page vide n'est pas une page.
        if ($rubrique !== null && $articles === []) {
            throw $this->createNotFoundException('Rubrique sans article.');
        }

        foreach ($articles as &$article) {
            $article['date_fr'] = $this->dateFrancaise($article['cree_le']);
            $article['extrait'] = $this->extrait((string) $article['corps']);
        }
        unset($article);

        $nomRubrique = null;
        if ($rubrique !== null) {
            $nomRubrique = $articles[0]['rubrique_nom'];
        }

        $filAriane = [
            ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
            ['nom' => 'Blog', 'url' => $this->generateUrl('blog_index')],
        ];
        if ($rubrique !== null) {
            $filAriane[] = ['nom' => $nomRubrique, 'url' => $this->generateUrl('blog_categorie', ['rubrique' => $rubrique])];
        }

        $response = $this->render('blog/index.html.twig', [
            'page' => $rubrique ?? 'index',
            'rubrique_nom' => $nomRubrique,
            'rubrique_sous_titre' => $detailRubrique['sous_titre'] ?? null,
            'description' => $detailRubrique['description']
                ?? "Découvrez l'actualité politique de l'Assemblée nationale, du gouvernement et des députés avec les articles de Datan.",
            'articles' => $articles,
            'fil_ariane' => $filAriane,
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /** « 2025-09-09 … » → « 09 septembre 2025 », zéro de tête compris, comme le site. */
    private function dateFrancaise(?string $date): string
    {
        if ($date === null) {
            return '';
        }

        static $mois = [
            1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
            'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre',
        ];

        $horodate = strtotime($date);

        if ($horodate === false) {
            return '';
        }

        return \sprintf('%s %s %s', date('d', $horodate), $mois[(int) date('n', $horodate)], date('Y', $horodate));
    }

    /** Extrait des cartes : les 25 premiers mots du corps, débarrassé de son HTML. */
    private function extrait(string $corps): string
    {
        $texte = trim((string) preg_replace('/\s+/u', ' ', strip_tags($corps)));
        $mots = explode(' ', $texte);

        if (\count($mots) <= self::MOTS_EXTRAIT) {
            return $this->entitesDecodees($texte);
        }

        return $this->entitesDecodees(implode(' ', \array_slice($mots, 0, self::MOTS_EXTRAIT))) . '…';
    }

    /**
     * Description de la page d'un article : le début du corps, coupé au dernier
     * mot entier sous 300 caractères — le `character_limiter` de l'origine,
     * sans suffixe.
     */
    private function description(string $corps): string
    {
        $texte = trim((string) preg_replace('/\s+/u', ' ', strip_tags($corps)));

        if (mb_strlen($texte) <= self::CARACTERES_DESCRIPTION) {
            return $texte;
        }

        $coupe = mb_substr($texte, 0, self::CARACTERES_DESCRIPTION + 1);
        $dernierEspace = mb_strrpos($coupe, ' ');

        return $this->entitesDecodees(
            rtrim(mb_substr($coupe, 0, $dernierEspace === false ? self::CARACTERES_DESCRIPTION : $dernierEspace)),
        );
    }

    /**
     * Rend leur caractère aux entités HTML du corps de l'article.
     *
     * `strip_tags()` ôte les balises mais laisse les entités : le corps saisi
     * dans l'éditeur de la rédaction est truffé de `&nbsp;`, et un extrait passé
     * tel quel à Twig ressort avec « &nbsp; » écrit en toutes lettres au milieu
     * de la phrase — l'échappement automatique le change en `&amp;nbsp;`.
     * L'origine, elle, échappe son extrait (`<?= … ?>` nu), si bien que le
     * navigateur le rend en espace : c'est ce résultat-là qu'on reproduit, en
     * décodant ici, l'échappement de Twig se chargeant ensuite de la sûreté.
     *
     * Le décodage vient APRÈS la troncature, jamais avant : couper sur le texte
     * décodé déplacerait les frontières de mots (`&nbsp;Si` est un seul mot pour
     * le `word_limiter` de l'origine, deux une fois décodé) et l'extrait ne
     * s'arrêterait plus au même endroit que sur datan.fr.
     */
    private function entitesDecodees(string $texte): string
    {
        return html_entity_decode($texte, \ENT_QUOTES | \ENT_HTML5, 'UTF-8');
    }

    /** Adresse absolue d'un fichier public, pour les balises Open Graph. */
    private function urlAbsolue(string $chemin): string
    {
        $requete = $this->container->get('request_stack')->getCurrentRequest();

        return ($requete !== null ? $requete->getSchemeAndHttpHost() : 'https://datan.fr') . '/' . ltrim($chemin, '/');
    }
}
