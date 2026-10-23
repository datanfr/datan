<?php

namespace App\Controller;

use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Recherche transverse du site, portée du contrôleur Search de l'application
 * d'origine (Search.php + Search_model::searchInAll / sort).
 *
 * Deux entrées : `/search_api` (JSON, l'autocomplétion des barres de recherche)
 * et `/recherche/{q}` (la page de résultats). Elles partagent le même balayage
 * sur six familles — députés, groupes, villes, départements, votes décryptés,
 * articles — dans cet ordre, celui de l'UNION du modèle d'origine.
 *
 * Pas de normalisation PHP des accents ni de la casse : les collations MariaDB
 * s'en chargent (piège Corse du CLAUDE.md — ce qui casse est toujours hors de la
 * base). On se contente d'un `REPLACE('-', ' ')` sur les noms composés, comme le
 * legacy, pour qu'« aubervilliers » retrouve « Aubervilliers » et
 * « pierre-antoine » « Pierre Antoine ».
 */
class SearchController extends AbstractController
{
    /** Autant de résultats par famille que le legacy dans l'autocomplétion. */
    private const MAX_CATEGORIE_API = 5;

    /** Plafond global de l'autocomplétion (Search_model::searchInAll, total_max). */
    private const MAX_TOTAL_API = 10;

    /**
     * Plafond par famille sur la page de résultats. Le legacy n'en met aucun
     * (category_max = NULL) et embarque tout le jeu dans le HTML pour son
     * « voir plus » ; une recherche large (« saint », des centaines de communes)
     * gonflerait alors la page sans profit. Cent laisse dix pages de « voir
     * plus » — divergence assumée, la seule face au legacy sur cette page.
     */
    private const MAX_CATEGORIE_PAGE = 100;

    /** Page de texte à contenu variable : cache court, indexé à part (noindex). */
    private const CACHE_TTL = 3600;

    /** Fenêtre de l'extrait contextuel des votes et articles, en caractères. */
    private const EXTRAIT = 200;

    /**
     * Familles de résultats, dans l'ordre de l'UNION d'origine. Le nom et l'icône
     * sont ceux de `Search_model::sort()`.
     */
    private const FAMILLES = [
        'depute' => ['nom' => 'Députés', 'icone' => 'person-fill'],
        'groupe' => ['nom' => 'Groupes politiques', 'icone' => 'people-fill'],
        'ville' => ['nom' => 'Villes', 'icone' => 'house-door-fill'],
        'dpt' => ['nom' => 'Départements', 'icone' => 'house-door-fill'],
        'vote' => ['nom' => 'Votes', 'icone' => 'file-text-fill'],
        'blog' => ['nom' => 'Articles sur Datan', 'icone' => 'file-text-fill'],
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Endpoint de l'autocomplétion. `dist/autocomplete_search.js` l'interroge en
     * `GET /search_api?q=…[&type=ville]` et attend un tableau JSON d'objets
     * `{text, url, source}` — `text` étant le libellé déjà surligné, `url` une
     * adresse SANS barre initiale (le script la préfixe d'un `/`, relatif à
     * l'origine servie — surtout pas de `base_url` absolue, qui ferait sortir
     * la préproduction vers datan.fr).
     *
     * Pas d'en-tête de cache : comme le legacy, la réponse est vive (le proxy
     * intégré ne met en cache que ce qui porte un `s-maxage`). La sortie est
     * produite par `json_encode` brut — barres et accents échappés (`\/`,
     * `\uXXXX`), pas les chevrons — pour rester à l'octet près la réponse
     * d'origine, qu'aucun consommateur tiers n'a à voir bouger.
     */
    #[Route('/search_api', name: 'search_api', methods: ['GET'])]
    public function indexApi(Request $request): Response
    {
        $recherche = (string) $request->query->get('q', '');
        $type = $request->query->get('type');

        $resultats = [];

        foreach ($this->chercher($recherche, self::MAX_CATEGORIE_API, self::MAX_TOTAL_API) as $ligne) {
            if ($type === 'ville' && $ligne['source'] !== 'ville') {
                continue; // le champ « commune » ne remonte que des villes
            }

            $url = $ligne['url'];

            // Le champ commune des pages d'élections mène à /elections/resultats,
            // pas à la fiche de ville sous /deputes (str_replace du legacy).
            if ($type === 'ville' && str_starts_with($url, 'deputes/')) {
                $url = 'elections/resultats/' . substr($url, \strlen('deputes/'));
            }

            $resultats[] = [
                'text' => $this->surligne($ligne['title_search'], $recherche),
                'url' => $url,
                'source' => $ligne['source'],
            ];
        }

        // json_encode sans drapeau : mêmes échappements que le legacy (`\/`,
        // `\uXXXX`, chevrons laissés bruts) — la réponse est à l'octet près.
        return new Response(
            (string) json_encode($resultats),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
        );
    }

    /**
     * Page de résultats. Adresse indexée du legacy, servie noindex/nofollow comme
     * lui (`seoNoFollow`). Le motif `{q}` s'arrête au premier `/` : aucune route
     * plus large ne la précède (le contrôleur des pages éditoriales n'a pas de
     * fourre-tout), rien à départager par `priority:`.
     */
    #[Route('/recherche/{q}', name: 'search_index', methods: ['GET'])]
    public function index(string $q): Response
    {
        $resultats = $this->chercher($q, self::MAX_CATEGORIE_PAGE, null);

        $response = $this->render('search/index.html.twig', [
            'query' => $q,
            'count' => \count($resultats),
            'max_entries' => 10,
            'familles' => $this->grouper($resultats, $q),
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Recherche', 'url' => $this->generateUrl('search_index', ['q' => $q])],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Balayage des six familles, concaténées dans l'ordre de l'UNION d'origine.
     *
     * Le legacy fait une seule requête UNION ; six requêtes distinctes rendent le
     * même résultat, avec l'avantage que le tri par famille (villes par longueur
     * puis population, départements par nom) est toujours honoré — un `ORDER BY`
     * dans un membre d'UNION ne l'est, sous MariaDB, qu'accompagné d'un `LIMIT`.
     *
     * @return list<array{source: string, title: string, title_search: string, description: string, url: string}>
     */
    private function chercher(string $terme, ?int $parCategorie, ?int $total): array
    {
        if (trim($terme) === '') {
            return [];
        }

        $termeEspaces = str_replace('-', ' ', $terme);
        $prefixeEspaces = $this->echapperLike($termeEspaces) . '%';
        $prefixeBrut = $this->echapperLike($terme) . '%';
        $contient = '%' . $this->echapperLike($terme) . '%';

        $limite = $parCategorie !== null ? ' LIMIT ' . $parCategorie : '';
        $premiere = Legislature::PREMIERE;

        $resultats = [];

        // 1. Députés — sur le nom, dans un sens comme dans l'autre (« Panot
        // Mathilde » autant que « Mathilde Panot »). La législature est celle du
        // dernier mandat ; l'abréviation de groupe vient du rattachement courant
        // (`depute.groupe_id`), renseigné pour les députés en exercice. Un ancien
        // député n'a plus de groupe courant : sa ligne sort sans abréviation, là
        // où `deputes_last` gardait celle de sa dernière législature — écart mineur.
        $resultats = array_merge($resultats, $this->connection->fetchAllAssociative(
            "SELECT 'depute' AS source,
                    CONCAT(d.firstname, ' ', d.lastname) AS title,
                    CONCAT(d.firstname, ' ', d.lastname) AS title_search,
                    CONCAT('Législature ', dl.legislature_last, ' - ', COALESCE(g.libelle_abrev, '')) AS description,
                    CONCAT('deputes/', d.dpt_slug, '/depute_', d.slug) AS url
             FROM depute d
             JOIN (SELECT depute_id, MAX(legislature) AS legislature_last FROM mandat GROUP BY depute_id) dl
                  ON dl.depute_id = d.id
             LEFT JOIN groupe g ON g.id = d.groupe_id
             WHERE d.dpt_slug IS NOT NULL
               AND (CONCAT(REPLACE(d.firstname, '-', ' '), ' ', REPLACE(d.lastname, '-', ' ')) LIKE ?
                 OR CONCAT(REPLACE(d.lastname, '-', ' '), ' ', REPLACE(d.firstname, '-', ' ')) LIKE ?)
             ORDER BY d.lastname, d.firstname" . $limite,
            [$prefixeEspaces, $prefixeEspaces],
        ));

        // 2. Groupes politiques — nom (le legacy y met un MATCH plein texte,
        // remplacé par un LIKE « contient » faute d'index FULLTEXT sur la table)
        // ou abréviation en préfixe. Toutes législatures depuis la 14e.
        $resultats = array_merge($resultats, $this->connection->fetchAllAssociative(
            "SELECT 'groupe' AS source,
                    CONCAT(g.libelle, ' (', g.libelle_abrev, ')') AS title,
                    CONCAT(g.libelle, ' (', g.libelle_abrev, ')') AS title_search,
                    CONCAT('Législature ', g.legislature) AS description,
                    CONCAT('groupes/legislature-', g.legislature, '/', LOWER(g.libelle_abrev)) AS url
             FROM groupe g
             WHERE g.legislature >= " . $premiere . " AND (g.libelle LIKE ? OR g.libelle_abrev LIKE ?)
             ORDER BY g.date_debut DESC" . $limite,
            [$contient, $prefixeBrut],
        ));

        // 3. Villes — le nom en préfixe. Tri d'origine : la commune dont le nom
        // colle le mieux à la requête d'abord (écart de longueur), puis la plus
        // peuplée. `commune` porte déjà une ligne par commune, sans le GROUP BY
        // que le legacy devait faire sur sa table commune×circonscription.
        $resultats = array_merge($resultats, $this->connection->fetchAllAssociative(
            "SELECT 'ville' AS source,
                    c.nom AS title,
                    CONCAT(c.nom, ' (', dep.code, ')') AS title_search,
                    CONCAT(dep.nom, ' (', dep.code, ')') AS description,
                    CONCAT('deputes/', dep.slug, '/ville_', c.slug) AS url
             FROM commune c
             JOIN departement dep ON dep.id = c.departement_id
             WHERE REPLACE(c.nom, '-', ' ') LIKE ?
             ORDER BY LENGTH(c.nom) - LENGTH(?), c.population DESC" . $limite,
            [$prefixeEspaces, $terme],
        ));

        // 4. Départements — nom en préfixe ou code exact, par ordre alphabétique.
        $resultats = array_merge($resultats, $this->connection->fetchAllAssociative(
            "SELECT 'dpt' AS source,
                    CONCAT(dep.nom, ' (', dep.code, ')') AS title,
                    CONCAT(dep.nom, ' (', dep.code, ')') AS title_search,
                    '' AS description,
                    CONCAT('deputes/', dep.slug) AS url
             FROM departement dep
             WHERE REPLACE(dep.nom, '-', ' ') LIKE ? OR dep.code LIKE ?
             ORDER BY dep.nom ASC" . $limite,
            [$prefixeEspaces, $prefixeBrut],
        ));

        // 5. Votes décryptés — titre ou description. Deux corrections face au
        // legacy : on n'expose que le publié (sa requête ne filtrait pas l'état,
        // au risque de sortir un brouillon de la rédaction) ; et l'adresse d'un
        // vote du Congrès (numéro négatif) prend la forme `vote_cN`, sans quoi le
        // lien casse (route chiffres-seuls). Tri par récence, le MATCH n'étant
        // pas reproductible sans index FULLTEXT.
        $resultats = array_merge($resultats, $this->connection->fetchAllAssociative(
            "SELECT 'vote' AS source,
                    dcr.title AS title,
                    dcr.title AS title_search,
                    dcr.description AS description,
                    CONCAT('votes/legislature-', dcr.legislature, '/vote_',
                           IF(dcr.vote_numero < 0, CONCAT('c', ABS(dcr.vote_numero)), dcr.vote_numero)) AS url
             FROM decryptage dcr
             JOIN scrutin s ON s.id = dcr.scrutin_id
             WHERE dcr.state = 'published' AND (dcr.title LIKE ? OR dcr.description LIKE ?)
             ORDER BY s.date_scrutin DESC" . $limite,
            [$contient, $contient],
        ));

        // 6. Articles du blog — titre ou corps. L'adresse prend la vraie rubrique
        // de l'article (`blog/{rubrique}/{slug}`) et non le « rapports » codé en
        // dur du legacy : notre route valide la rubrique, un mauvais segment y
        // ferait un 404 là où le legacy l'ignorait.
        $resultats = array_merge($resultats, $this->connection->fetchAllAssociative(
            "SELECT 'blog' AS source,
                    a.titre AS title,
                    a.titre AS title_search,
                    a.corps AS description,
                    CONCAT('blog/', c.slug, '/', a.slug) AS url
             FROM article a
             JOIN categorie_article c ON c.id = a.categorie_id
             WHERE a.etat = 'published' AND (a.titre LIKE ? OR a.corps LIKE ?)
             ORDER BY a.cree_le DESC" . $limite,
            [$contient, $contient],
        ));

        return $total !== null ? \array_slice($resultats, 0, $total) : $resultats;
    }

    /**
     * Regroupe les résultats par famille pour la page, dans l'ordre fixe de
     * `sort()` : chaque famille garde sa clé, son nom, son icône et ses lignes
     * surlignées. Vote et article reçoivent un extrait contextuel autour du terme.
     *
     * @param list<array<string, mixed>> $resultats
     *
     * @return array<string, array{nom: string, icone: string, resultats: list<array{title: string, description: string, url: string}>}>
     */
    private function grouper(array $resultats, string $recherche): array
    {
        $familles = [];
        foreach (self::FAMILLES as $cle => $meta) {
            $familles[$cle] = ['nom' => $meta['nom'], 'icone' => $meta['icone'], 'resultats' => []];
        }

        foreach ($resultats as $ligne) {
            $source = $ligne['source'];
            $description = strip_tags((string) $ligne['description']);

            if ($source === 'vote' || $source === 'blog') {
                $description = $this->extrait($description, $recherche);
            }

            $familles[$source]['resultats'][] = [
                'title' => $this->surligne((string) $ligne['title'], $recherche),
                'description' => $this->surligne($description, $recherche),
                'url' => $ligne['url'],
            ];
        }

        return $familles;
    }

    /**
     * Extrait de 200 caractères autour de la première occurrence du terme, ou le
     * début du texte à défaut (character_limiter du legacy). Le legacy découpe en
     * octets, ce qui peut trancher un accent en deux : on découpe en caractères,
     * défaut corrigé au passage.
     */
    private function extrait(string $texte, string $recherche): string
    {
        $position = $recherche !== '' ? mb_stripos($texte, $recherche) : false;

        // Le legacy teste `if ($position)` : une occurrence en tête (position 0)
        // est donc traitée comme une absence — quirk repris tel quel.
        if ($position) {
            $debut = max(0, $position - 100);
            $extrait = mb_substr($texte, $debut, self::EXTRAIT);

            if ($debut !== 0) {
                $extrait = '...' . $extrait;
            }
            if ($position + 100 < mb_strlen($texte)) {
                $extrait .= '...';
            }

            return $extrait;
        }

        return mb_strlen($texte) > self::EXTRAIT
            ? rtrim(mb_substr($texte, 0, mb_strrpos(mb_substr($texte, 0, self::EXTRAIT + 1), ' ') ?: self::EXTRAIT))
            : $texte;
    }

    /**
     * Surligne les occurrences du terme, comme `highlight_phrase()` de
     * CodeIgniter : remplacement insensible à la casse, sans drapeau `u` (le
     * legacy n'en met pas), le motif étant échappé pour rester littéral.
     */
    private function surligne(string $texte, string $phrase): string
    {
        if ($texte === '' || $phrase === '') {
            return $texte;
        }

        return preg_replace(
            '/(' . preg_quote($phrase, '/') . ')/i',
            '<span class="text-primary">$1</span>',
            $texte,
        ) ?? $texte;
    }

    /** Échappe les jokers LIKE (`\`, `%`, `_`), l'échappement `\` étant celui par défaut de MariaDB. */
    private function echapperLike(string $valeur): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $valeur);
    }
}
