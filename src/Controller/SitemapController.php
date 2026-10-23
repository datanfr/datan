<?php

namespace App\Controller;

use App\Commune\SeuilCommunes;
use App\Http\RechauffeurCache;
use App\Legislature;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Plans de site, portés du contrôleur Sitemap de l'application CodeIgniter
 * d'origine (routes `sitemap.xml` et `sitemap-*-1.xml`, routes.php:109-123).
 *
 * Le suffixe `-1` de chaque nom de fichier annonce une pagination que
 * l'application d'origine n'a jamais mise en place, et dont nous n'avons pas
 * besoin : le plus gros plan compte 18 311 adresses, loin des 50 000 que le
 * protocole autorise. Il est conservé pour ne pas changer des URL que les
 * moteurs connaissent déjà.
 *
 * **Règle de ce fichier : n'annoncer que ce qui répond 200.** Un plan qui
 * promet des 404 coûte plus qu'il ne rapporte. Chaque liste reprend donc le
 * critère de sélection de la page correspondante, et non un dénombrement plus
 * large — d'où, par exemple, l'exclusion des départements sans député en
 * exercice, sur lesquels {@see DepartementController::individual()} rend un 404.
 *
 * Les quatorze plans du site sont désormais tous servis, ceux du blog
 * (`sitemap-posts-1.xml`, `sitemap-categories-1.xml`) ayant rejoint la liste
 * avec le portage des pages `/blog` (23 juillet).
 */
class SitemapController extends AbstractController
{
    /** Les listes bougent au rythme de la synchronisation quotidienne. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /**
     * Prédicat « ce député siège encore ».
     *
     * Ni `depute.date_fin` ni `depute.groupe_id` ne disent qu'un député siège :
     * la première n'est pas rafraîchie aux réélections, le second ne porte que
     * l'appartenance courante. Seul un mandat sans date de fin fait foi.
     */
    private const EN_EXERCICE = 'EXISTS (SELECT 1 FROM mandat m
                                         WHERE m.depute_id = d.id
                                           AND m.legislature = :legislature
                                           AND m.date_fin IS NULL)';

    /**
     * Motif que `depute_individual` impose à ses deux segments. Un slug qui ne
     * s'y conforme pas n'a pas d'adresse du tout : la route ne le reconnaît pas
     * et la fiche répond 404, quoi que contienne la base.
     */
    private const SLUG_ROUTE = '/^[a-z0-9\-]+$/';

    /** Les cinq pages que porte chaque groupe parlementaire. */
    private const PAGES_GROUPE = [
        'groupe_individual',
        'groupe_membres',
        'groupe_statistiques',
        'groupe_votes',
        'groupe_votes_tous',
    ];

    /** Les douze plans que nous publions, dans l'ordre du fichier d'index. */
    private const PLANS = [
        'sitemap_deputes',
        'sitemap_deputes_inactifs',
        'sitemap_groupes',
        'sitemap_groupes_inactifs',
        'sitemap_partis',
        'sitemap_votes',
        'sitemap_departements',
        'sitemap_communes',
        'sitemap_elections',
        'sitemap_elections_departements',
        'sitemap_elections_communes',
        'sitemap_structure',
        'sitemap_blog_categories',
        'sitemap_blog_articles',
    ];

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/sitemap.xml', name: 'sitemap_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->xml('sitemap/index.xml.twig', [
            'sitemaps' => array_map($this->url(...), self::PLANS),
        ]);
    }

    #[Route('/sitemap-deputes-1.xml', name: 'sitemap_deputes', methods: ['GET'])]
    public function deputes(): Response
    {
        return $this->plan($this->adressesDeputes(actifs: true));
    }

    #[Route('/sitemap-deputes-inactifs-1.xml', name: 'sitemap_deputes_inactifs', methods: ['GET'])]
    public function deputesInactifs(): Response
    {
        return $this->plan($this->adressesDeputes(actifs: false));
    }

    #[Route('/sitemap-groupes-1.xml', name: 'sitemap_groupes', methods: ['GET'])]
    public function groupes(): Response
    {
        return $this->plan($this->adressesGroupes(actifs: true));
    }

    #[Route('/sitemap-groupes-inactifs-1.xml', name: 'sitemap_groupes_inactifs', methods: ['GET'])]
    public function groupesInactifs(): Response
    {
        return $this->plan($this->adressesGroupes(actifs: false));
    }

    #[Route('/sitemap-partis-politiques-1.xml', name: 'sitemap_partis', methods: ['GET'])]
    public function partis(): Response
    {
        $adresses = [$this->url('partis_index')];

        // `parti_individual` retrouve son parti en majuscules ; le site publie
        // la forme minuscule, c'est elle qui doit être annoncée.
        foreach ($this->connection->fetchFirstColumn('SELECT libelle_abrev FROM parti ORDER BY libelle_abrev') as $abrev) {
            $adresses[] = $this->url('parti_individual', ['abrev' => mb_strtolower((string) $abrev)]);
        }

        return $this->plan($adresses);
    }

    #[Route('/sitemap-votes-1.xml', name: 'sitemap_votes', methods: ['GET'])]
    public function votes(): Response
    {
        $adresses = [];

        // Un vote du Congrès porte le même numéro qu'un scrutin de l'Assemblée
        // et vit sous `vote_c<n>` : le préfixe d'uid est le seul discriminant.
        // Les confondre annoncerait deux fois la même adresse et en perdrait une.
        foreach ($this->connection->iterateAssociative(
            "SELECT legislature, numero, uid LIKE 'VTCGR%' AS congres
             FROM scrutin
             ORDER BY legislature, numero",
        ) as $scrutin) {
            $adresses[] = $this->url($scrutin['congres'] ? 'vote_congres' : 'vote_individual', [
                'legislature' => (int) $scrutin['legislature'],
                'numero' => (int) $scrutin['numero'],
            ]);
        }

        return $this->plan($adresses);
    }

    #[Route('/sitemap-localites-d-1.xml', name: 'sitemap_departements', methods: ['GET'])]
    public function departements(): Response
    {
        $slugs = $this->connection->fetchFirstColumn(
            'SELECT dep.slug
             FROM departement dep
             WHERE EXISTS (SELECT 1 FROM depute d
                           WHERE d.departement_code = dep.code AND ' . self::EN_EXERCICE . ')
             ORDER BY dep.code',
            ['legislature' => Legislature::COURANTE],
        );

        return $this->plan(array_map(
            fn (string $slug) => $this->url('departement_individual', ['departement' => $slug]),
            $slugs,
        ));
    }

    #[Route('/sitemap-localites-v-1.xml', name: 'sitemap_communes', methods: ['GET'])]
    public function communes(): Response
    {
        return $this->plan(array_map(
            fn (array $c) => $this->url('commune_individual', ['departement' => $c['departement'], 'commune' => $c['commune']]),
            $this->communesLiees(DepartementController::POPULATION_MINIMALE, false, 'departement, commune'),
        ));
    }

    /**
     * Communes qu'une page lie en clair, et donc qu'un plan peut annoncer :
     * celles au-dessus du seuil de population de cette page — ou, pour les cinq
     * collectivités qui n'en ont aucune, les vingt premières ({@see SeuilCommunes}),
     * exactement celles que le bloc des communes affiche : même rang, calculé par
     * département dans l'ordre des pages (`population DESC, nom`).
     *
     * `DISTINCT` et non `GROUP BY` par confort : Château-Chinon (Ville) et
     * Château-Chinon (Campagne) partagent slug et département, et n'ont donc
     * qu'une adresse à elles deux. Le code du département est dans le `DISTINCT`
     * pour pouvoir trier dessus — il ne dédouble rien, un slug n'ayant qu'un code.
     *
     * @return list<array{departement: string, code: string, commune: string}>
     */
    private function communesLiees(int $seuil, bool $sansParentheses, string $ordre): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT DISTINCT departement, code, commune
             FROM (SELECT dep.slug AS departement, dep.code AS code, c.slug AS commune, c.population,
                          dep.slug IN (:exemptes) AS exemptee,
                          ROW_NUMBER() OVER (PARTITION BY c.departement_id ORDER BY c.population DESC, c.nom) AS rang
                   FROM commune c
                   JOIN departement dep ON dep.id = c.departement_id) t
             WHERE ((exemptee = 1 AND rang <= :plafond) OR (exemptee = 0 AND population > :seuil))'
            . ($sansParentheses ? ' AND commune NOT LIKE :parenthese' : '') . '
             ORDER BY ' . $ordre,
            [
                'exemptes' => SeuilCommunes::SANS_SEUIL,
                'plafond' => SeuilCommunes::PLAFOND,
                'seuil' => $seuil,
                'parenthese' => '%(%',
            ],
            ['exemptes' => ArrayParameterType::STRING],
        );
    }

    #[Route('/sitemap-elections-1.xml', name: 'sitemap_elections', methods: ['GET'])]
    public function elections(): Response
    {
        $adresses = [$this->url('elections_index')];

        foreach ($this->connection->fetchFirstColumn('SELECT slug FROM election ORDER BY annee DESC, date_tour1 DESC') as $slug) {
            $adresses[] = $this->url('elections_individual', ['slug' => $slug]);
        }

        return $this->plan($adresses);
    }

    /**
     * Départements, moins Paris : il est à lui seul son unique commune, et
     * {@see ElectionController::resultatsDepartement()} y rend un 404 comme le
     * site. C'est la seule exclusion — la page ne demande aucun résultat, juste
     * la liste des communes, et un département sans député en a quand même.
     */
    #[Route('/sitemap-elections-d-1.xml', name: 'sitemap_elections_departements', methods: ['GET'])]
    public function electionsDepartements(): Response
    {
        $slugs = $this->connection->fetchFirstColumn(
            "SELECT slug FROM departement WHERE slug <> 'paris-75' ORDER BY code",
        );

        return $this->plan(array_map(
            fn (string $slug) => $this->url('elections_resultats_departement', ['departement' => $slug]),
            $slugs,
        ));
    }

    /**
     * Fiches de résultats par commune — le deuxième plus gros plan du site,
     * 16 600 adresses (le plan des votes, 18 311, reste le plus grand).
     *
     * Le seuil est celui des liens que le site écrit en clair sur ses pages
     * d'élections, cinq cents habitants ({@see ElectionController::POPULATION_MINIMALE}),
     * huit fois plus bas que celui des fiches de ville de `/deputes`. Une
     * commune plus petite a bien sa page, mais rien n'y mène.
     *
     * Les trois communes à slug parenthésé dépassant le seuil en sont écartées
     * — Assions (Les), Ollières-sur-Eyrieux (Les), Bonvillers (Mont) :
     * datan.fr renvoie 400 sur ces adresses (les parenthèses brutes sont rejetées
     * en amont de son routeur), et un plan n'annonce que ce que la référence sert
     * en 200. Nos pages les servent bien — la route accepte les parenthèses, cf.
     * {@see ElectionController::SLUG_COMMUNE} — mais on ne les référence pas.
     * Elles étaient cinq : Étoile (L') et Nonières (Les) ont retrouvé le slug
     * sans parenthèses que la production sert réellement, réparé à l'import
     * ({@see \App\Command\ImportCommunesCommand}), et rentrent donc dans le plan.
     */
    #[Route('/sitemap-elections-v-1.xml', name: 'sitemap_elections_communes', methods: ['GET'])]
    public function electionsCommunes(): Response
    {
        return $this->plan(array_map(
            fn (array $c) => $this->url('elections_resultats_commune', ['departement' => $c['departement'], 'commune' => $c['commune']]),
            $this->communesLiees(ElectionController::POPULATION_MINIMALE, true, 'code, commune'),
        ));
    }

    /**
     * Rubriques du blog. Seules celles ayant au moins un article publié : une
     * rubrique vide répond 404 ({@see BlogController::liste()}), et un plan
     * n'annonce que du 200.
     *
     * `/blog` n'est pas ici mais dans le plan structure, comme sur le site :
     * l'y répéter annoncerait deux fois la même adresse.
     */
    #[Route('/sitemap-categories-1.xml', name: 'sitemap_blog_categories', methods: ['GET'])]
    public function blogCategories(): Response
    {
        $adresses = [];

        foreach ($this->connection->fetchFirstColumn(
            "SELECT c.slug
             FROM categorie_article c
             WHERE EXISTS (SELECT 1 FROM article a WHERE a.categorie_id = c.id AND a.etat = 'published')
             ORDER BY c.slug",
        ) as $slug) {
            $adresses[] = $this->url('blog_categorie', ['rubrique' => $slug]);
        }

        return $this->plan($adresses);
    }

    #[Route('/sitemap-posts-1.xml', name: 'sitemap_blog_articles', methods: ['GET'])]
    public function blogArticles(): Response
    {
        $articles = $this->connection->fetchAllAssociative(
            "SELECT c.slug AS rubrique, a.slug
             FROM article a
             JOIN categorie_article c ON c.id = a.categorie_id
             WHERE a.etat = 'published'
             ORDER BY a.cree_le DESC",
        );

        return $this->plan(array_map(
            fn (array $a) => $this->url('blog_article', $a),
            $articles,
        ));
    }

    #[Route('/sitemap-structure-1.xml', name: 'sitemap_structure', methods: ['GET'])]
    public function structure(): Response
    {
        // Les pages fixes annoncées par le site le restent ici : le
        // référencement est le fonds de commerce de Datan, et son plan
        // structure liste /a-propos, /faq, /mentions-legales, /soutenir,
        // /parrainages-2022, /outils/coalition-simulateur et /blog — sept
        // adresses qui manquaient à l'appel alors qu'elles répondent 200.
        $adresses = [
            $this->url('home'),
            $this->url('deputes_index'),
            $this->url('deputes_inactifs'),
            $this->url('departements_index'),
            $this->url('groupes_index'),
            $this->url('groupes_inactifs'),
            $this->url('votes_index'),
            $this->url('votes_decryptes'),
            $this->url('partis_index'),
            $this->url('commissions_index'),
            $this->url('classement_index'),
            $this->url('page_statistiques'),
            $this->url('page_a_propos'),
            $this->url('faq_index'),
            $this->url('page_mentions_legales'),
            $this->url('page_soutenir'),
            $this->url('parrainages_index'),
            $this->url('outils_coalition_simulateur'),
            $this->url('blog_index'),
        ];

        // La législature courante n'a pas d'adresse en `legislature-17` : les
        // deux listes y redirigent vers `/deputes` et `/groupes`.
        foreach (Legislature::publiees() as $legislature) {
            if ($legislature !== Legislature::COURANTE) {
                $adresses[] = $this->url('deputes_legislature', ['legislature' => $legislature]);
                $adresses[] = $this->url('groupes_legislature', ['legislature' => $legislature]);
            }
        }

        foreach ($this->connection->fetchFirstColumn('SELECT slug FROM commission ORDER BY slug') as $slug) {
            $adresses[] = $this->url('commission_individual', ['slug' => $slug]);
        }

        foreach ($this->connection->fetchFirstColumn('SELECT slug FROM categorie ORDER BY slug') as $slug) {
            $adresses[] = $this->url('votes_categorie', ['slug' => $slug]);
        }

        foreach (array_keys(ClassementController::PAGES) as $page) {
            $adresses[] = $this->url('classement_individual', ['page' => $page]);
        }

        return $this->plan([...$adresses, ...$this->adressesArchives()]);
    }

    /**
     * Fiches de députés, l'une des deux moitiés de la population.
     *
     * Deux exclusions, toutes deux pour ne pas annoncer une adresse morte :
     *
     * - le député sans `dpt_slug`, dont la fiche répond à n'importe quelle
     *   adresse de département faute de forme canonique vers laquelle
     *   {@see DeputeController::individual()} puisse rediriger — en annoncer une
     *   choisie au hasard reviendrait à publier du contenu dupliqué ;
     * - le député dont le `dpt_slug` sort du motif de la route. Garde sans cas
     *   connu depuis la correction des slugs corses (`ImportMandatsCommand` ne
     *   minusculisait que le nom du département, pas le code : `haute-corse-2B`)
     *   — conservée parce qu'écrite sur le motif, elle attrapera toute récidive
     *   au lieu de publier une adresse morte.
     *
     * @return list<string>
     */
    private function adressesDeputes(bool $actifs): array
    {
        $deputes = $this->connection->fetchAllAssociative(
            'SELECT d.id, d.slug, d.dpt_slug
             FROM depute d
             WHERE d.dpt_slug IS NOT NULL AND ' . ($actifs ? '' : 'NOT ') . self::EN_EXERCICE . '
             ORDER BY d.lastname, d.firstname',
            ['legislature' => Legislature::COURANTE],
        );

        $adresses = [];
        $identifiants = [];

        foreach ($deputes as $depute) {
            if (!preg_match(self::SLUG_ROUTE, (string) $depute['dpt_slug'])
                || !preg_match(self::SLUG_ROUTE, (string) $depute['slug'])) {
                continue;
            }

            $parametres = ['dptSlug' => $depute['dpt_slug'], 'slug' => $depute['slug']];
            $identifiants[(int) $depute['id']] = $parametres;

            $adresses[] = $this->url('depute_individual', $parametres);
            $adresses[] = $this->url('depute_votes', $parametres);
        }

        foreach ($this->legislaturesPassees(array_keys($identifiants)) as $ligne) {
            $adresses[] = $this->url('depute_legislature', $identifiants[(int) $ligne['depute_id']] + [
                'legislature' => (int) $ligne['legislature'],
            ]);
        }

        return $adresses;
    }

    /**
     * Législatures pour lesquelles un député a une page d'archive.
     *
     * Sa législature la plus récente en est exclue : elle redirige en 301 vers
     * la fiche principale. Celles d'avant la 14e aussi — le site ne les publie
     * pas, et rien n'y mène.
     *
     * @param list<int> $deputes
     *
     * @return list<array<string, mixed>>
     */
    private function legislaturesPassees(array $deputes): array
    {
        if ($deputes === []) {
            return [];
        }

        return $this->connection->fetchAllAssociative(
            'SELECT DISTINCT m.depute_id, m.legislature
             FROM mandat m
             WHERE m.depute_id IN (:deputes)
               AND m.legislature >= :premiere
               AND m.legislature < (SELECT MAX(m2.legislature) FROM mandat m2 WHERE m2.depute_id = m.depute_id)
             ORDER BY m.depute_id, m.legislature DESC',
            ['deputes' => $deputes, 'premiere' => Legislature::PREMIERE],
            ['deputes' => ArrayParameterType::INTEGER],
        );
    }

    /**
     * Les cinq pages d'un groupe parlementaire.
     *
     * Est actif le seul groupe encore ouvert de la législature en cours : un
     * groupe d'une législature révolue appartient à l'histoire, même si sa
     * `date_fin` est restée vide.
     *
     * @return list<string>
     */
    private function adressesGroupes(bool $actifs): array
    {
        // Le sigle seul ne désigne pas un groupe : « LR » existe à la 15e, à la
        // 16e et à la 17e législature. C'est le couple qui fait l'adresse.
        $groupes = $this->connection->fetchAllAssociative(
            'SELECT g.legislature, LOWER(g.libelle_abrev) AS abrev
             FROM groupe g
             WHERE g.legislature BETWEEN :premiere AND :courante
               AND ' . ($actifs ? '' : 'NOT ') . '(g.date_fin IS NULL AND g.legislature = :courante)
             ORDER BY g.legislature DESC, abrev',
            ['premiere' => Legislature::PREMIERE, 'courante' => Legislature::COURANTE],
        );

        $adresses = [];

        foreach ($groupes as $groupe) {
            $parametres = ['legislature' => (int) $groupe['legislature'], 'abrev' => $groupe['abrev']];

            foreach (self::PAGES_GROUPE as $route) {
                $adresses[] = $this->url($route, $parametres);
            }
        }

        return $adresses;
    }

    /**
     * Archives de votes par année et par mois, pour les seules périodes où un
     * scrutin a eu lieu : les pages vides répondent 200 mais n'ont rien à
     * offrir à un moteur.
     *
     * @return list<string>
     */
    private function adressesArchives(): array
    {
        $periodes = $this->connection->fetchAllAssociative(
            'SELECT legislature, YEAR(date_scrutin) AS annee, MONTH(date_scrutin) AS mois
             FROM scrutin
             WHERE date_scrutin IS NOT NULL
             GROUP BY legislature, annee, mois
             ORDER BY legislature, annee, mois',
        );

        $adresses = [];
        $vues = [];

        foreach ($periodes as $periode) {
            $legislature = (int) $periode['legislature'];
            $annee = (int) $periode['annee'];

            if (!isset($vues[$legislature])) {
                $vues[$legislature] = true;
                $adresses[] = $this->url('votes_legislature', ['legislature' => $legislature]);
            }

            if (!isset($vues["$legislature-$annee"])) {
                $vues["$legislature-$annee"] = true;
                $adresses[] = $this->url('votes_annee', ['legislature' => $legislature, 'annee' => $annee]);
            }

            // Mois sur deux chiffres (`2026/07`, jamais `2026/7`) : c'est la
            // forme que le site annonce et que les moteurs indexent depuis des
            // années. La route accepte les deux graphies et les sert en 200 —
            // le plan n'en publie qu'une, l'historique, sous peine de faire
            // naître un doublon d'adresses.
            $adresses[] = $this->url('votes_mois', [
                'legislature' => $legislature,
                'annee' => $annee,
                'mois' => sprintf('%02d', (int) $periode['mois']),
            ]);
        }

        return $adresses;
    }

    /**
     * @param array<string, mixed> $parametres
     */
    private function url(string $route, array $parametres = []): string
    {
        return $this->generateUrl($route, $parametres, UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param list<string> $adresses
     */
    private function plan(array $adresses): Response
    {
        return $this->xml('sitemap/page.xml.twig', ['urls' => $adresses]);
    }

    /**
     * @param array<string, mixed> $parametres
     */
    private function xml(string $gabarit, array $parametres): Response
    {
        $response = $this->render($gabarit, $parametres);
        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');
        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }
}
