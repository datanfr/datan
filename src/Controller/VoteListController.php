<?php

namespace App\Controller;

use App\Http\RechauffeurCache;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Liste des scrutins d'une législature (Votes::all de l'application CodeIgniter
 * d'origine, vue application/views/votes/all_an.php).
 *
 * La 17e législature compte à elle seule plus de 8 000 scrutins : la liste est
 * lue en une seule requête, sans hydratation ORM, et la pagination reste côté
 * client (DataTables) comme sur le site d'origine.
 */
class VoteListController extends AbstractController
{
    /** Les scrutins passés ne bougent plus, seul le haut de liste s'allonge. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /** Seuls les décryptages publiés sortent du back-office de la rédaction. */
    private const PUBLIE = 'published';

    /** Mois tels que les orthographie l'application d'origine (get_months()). */
    private const MOIS = [
        1 => 'janvier', 'février', 'mars', 'avril', 'mai', 'juin',
        'juillet', 'août', 'septembre', 'octobre', 'novembre', 'decembre',
    ];

    /**
     * Titre d'un scrutin tel que l'affiche l'application d'origine : le REPLACE
     * corrige les « n? » de l'open data qu'il faut lire « n° », et l'initiale
     * est remise en capitale — tous les titres arrivent en minuscule.
     */
    private const TITRE_SQL = "REPLACE(s.titre, 'n?', 'n°')";

    /**
     * Numéro de scrutin signé, négatif pour un vote du Congrès.
     *
     * Les deux assemblées numérotent leurs scrutins chacune de son côté : le
     * scrutin n° 1 de la 16e législature désigne à la fois une motion de censure
     * et la révision constitutionnelle du 4 mars 2024. Seul le préfixe d'uid les
     * sépare, et c'est ce signe que `lien_vote()` et le filtre `congress_numero`
     * attendent pour choisir l'adresse et l'affichage.
     */
    private const NUMERO_SQL = "CASE WHEN s.uid LIKE 'VTCGR%' THEN -s.numero ELSE s.numero END";

    /** Décryptages du carrousel de tête, du plus récent au plus ancien. */
    private const CARROUSEL = 7;

    /** Décryptages montrés par catégorie sur l'accueil de la rubrique. */
    private const PAR_CATEGORIE = 2;

    /** Scrutins du second carrousel. */
    private const DERNIERS_SCRUTINS = 10;

    /** Longueur des titres de scrutin dans les cartes (word_limiter()). */
    private const MOTS_TITRE = 20;

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/votes', name: 'votes_index', methods: ['GET'])]
    public function index(): Response
    {
        $response = $this->render('vote/index.html.twig', [
            'legislature' => Legislature::COURANTE,
            'decryptages' => $this->derniersDecryptages(self::CARROUSEL),
            'categories' => $this->parCategorie(),
            'scrutins' => $this->derniersScrutins(self::DERNIERS_SCRUTINS),
            'archives' => $this->archives(Legislature::COURANTE),
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Votes', 'url' => $this->generateUrl('votes_index')],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    #[Route('/votes/decryptes/{slug}', name: 'votes_categorie', requirements: ['slug' => '[a-z0-9\-]+'], methods: ['GET'])]
    public function categorie(string $slug): Response
    {
        $categorie = $this->connection->fetchAssociative(
            'SELECT id, name, slug, libelle FROM categorie WHERE slug = :slug',
            ['slug' => $slug],
        );

        $decryptages = $categorie ? $this->decryptagesDeLaCategorie((int) $categorie['id']) : [];

        // L'application d'origine rend un 404 sur une catégorie vide, et non une
        // page sans carte : une thématique sans décryptage n'est pas une page.
        if ($decryptages === []) {
            throw $this->createNotFoundException('Aucun vote décrypté sur cette thématique.');
        }

        $response = $this->render('vote/categorie.html.twig', [
            'legislature' => Legislature::COURANTE,
            'categorie' => $categorie,
            'decryptages' => $decryptages,
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Votes', 'url' => $this->generateUrl('votes_index')],
                ['nom' => 'Votes décryptés', 'url' => $this->generateUrl('votes_decryptes')],
                ['nom' => $categorie['name'], 'url' => $this->generateUrl('votes_categorie', ['slug' => $categorie['slug']])],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    #[Route('/votes/legislature-{legislature}', name: 'votes_legislature', requirements: ['legislature' => '\d+'], methods: ['GET'])]
    public function legislature(int $legislature): Response
    {
        return $this->liste($legislature, null, null);
    }

    #[Route('/votes/legislature-{legislature}/{annee}', name: 'votes_annee', requirements: ['legislature' => '\d+', 'annee' => '\d{4}'], methods: ['GET'])]
    public function annee(int $legislature, int $annee): Response
    {
        return $this->liste($legislature, $annee, null);
    }

    #[Route('/votes/legislature-{legislature}/{annee}/{mois}', name: 'votes_mois', requirements: ['legislature' => '\d+', 'annee' => '\d{4}', 'mois' => '\d{1,2}'], methods: ['GET'])]
    public function mois(int $legislature, int $annee, int $mois): Response
    {
        if ($mois < 1 || $mois > 12) {
            throw $this->createNotFoundException('Mois inconnu.');
        }

        return $this->liste($legislature, $annee, $mois);
    }

    private function liste(int $legislature, ?int $annee, ?int $mois): Response
    {
        if ($legislature < Legislature::PREMIERE) {
            throw $this->createNotFoundException('Législature inconnue.');
        }

        $archives = $this->archives($legislature);

        if ($archives === []) {
            throw $this->createNotFoundException('Aucun scrutin pour cette législature.');
        }

        $response = $this->render('vote/all.html.twig', [
            'legislature' => $legislature,
            'legislatures' => Legislature::publiees(),
            'annee' => $annee,
            'mois' => $mois,
            'mois_libelle' => $mois !== null ? self::MOIS[$mois] : null,
            'scrutins' => $this->scrutins($legislature, $annee, $mois),
            'archives' => $archives,
            // Le site d'origine ne rend cliquables que les 30 premières lignes
            // sur les pages qui listent une législature ou une année entière.
            'liens_limites' => $mois === null,
            'fil_ariane' => $this->filAriane($legislature, $annee, $mois),
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Fil d'Ariane des listes datées — « 16e législature » ici, quand les pages
     * de députés écrivent « 16ème » : l'incohérence est celle de l'origine.
     *
     * @return list<array{nom: string, url: string}>
     */
    private function filAriane(int $legislature, ?int $annee, ?int $mois): array
    {
        $fil = [
            ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
            ['nom' => 'Votes', 'url' => $this->generateUrl('votes_index')],
            ['nom' => $legislature . 'e législature', 'url' => $this->generateUrl('votes_legislature', ['legislature' => $legislature])],
        ];

        if ($annee !== null) {
            $fil[] = ['nom' => (string) $annee, 'url' => $this->generateUrl('votes_annee', ['legislature' => $legislature, 'annee' => $annee])];
        }

        if ($annee !== null && $mois !== null) {
            $fil[] = ['nom' => ucfirst(self::MOIS[$mois]), 'url' => $this->generateUrl('votes_mois', ['legislature' => $legislature, 'annee' => $annee, 'mois' => $mois])];
        }

        return $fil;
    }

    /**
     * Scrutins de la période demandée, du plus récent au plus ancien.
     *
     * La période est traduite en intervalle de dates plutôt qu'en YEAR()/MONTH()
     * pour que l'index sur `date_scrutin` reste utilisable.
     *
     * @return list<array<string, mixed>>
     */
    private function scrutins(int $legislature, ?int $annee, ?int $mois): array
    {
        $params = ['legislature' => $legislature, 'published' => self::PUBLIE];
        $periode = '';

        if ($annee !== null) {
            $debut = new \DateTimeImmutable(sprintf('%04d-%02d-01', $annee, $mois ?? 1));
            $fin = $debut->modify($mois !== null ? '+1 month' : '+1 year');

            $periode = ' AND s.date_scrutin >= :debut AND s.date_scrutin < :fin';
            $params['debut'] = $debut->format('Y-m-d');
            $params['fin'] = $fin->format('Y-m-d');
        }

        // Le formatage de la date et de l'initiale du titre est confié à la base :
        // sur 8 000 lignes, autant ne pas les reprendre une à une en PHP.
        return $this->connection->fetchAllAssociative(
            'SELECT ' . self::NUMERO_SQL . ' AS numero, s.sort_code,
                    DATE_FORMAT(s.date_scrutin, \'%d-%m-%Y\') AS date_fr,
                    CONCAT(UPPER(LEFT(' . self::TITRE_SQL . ', 1)),
                           SUBSTRING(' . self::TITRE_SQL . ', 2)) AS titre,
                    dcr.title AS decryptage_title
             FROM scrutin s
             LEFT JOIN decryptage dcr ON dcr.scrutin_id = s.id AND dcr.state = :published
             WHERE s.legislature = :legislature' . $periode . '
             ORDER BY s.date_scrutin DESC, s.numero DESC',
            $params,
        );
    }

    /**
     * Colonnes d'une carte de vote décrypté, la table `decryptage` devant être
     * aliasée `dcr` et son scrutin `s`.
     */
    private const CARTE_SQL = 'dcr.title, dcr.legislature, dcr.vote_numero,
                               s.date_scrutin, s.sort_code,
                               c.name AS categorie_name, l.name AS lecture_name';

    /**
     * Derniers votes décryptés, toutes thématiques confondues.
     *
     * @return list<array<string, mixed>>
     */
    private function derniersDecryptages(int $limite): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT ' . self::CARTE_SQL . '
             FROM decryptage dcr
             JOIN scrutin s ON s.id = dcr.scrutin_id
             LEFT JOIN categorie c ON c.id = dcr.categorie_id
             LEFT JOIN lecture l ON l.id = dcr.lecture_id
             WHERE dcr.state = :published
             ORDER BY dcr.legislature DESC, s.date_scrutin DESC, dcr.vote_numero DESC
             LIMIT ' . $limite,
            ['published' => self::PUBLIE],
        );
    }

    /**
     * Les deux derniers décryptages de chaque thématique.
     *
     * L'application d'origine lance une requête par catégorie ; une fonction de
     * fenêtrage les remplace toutes. Le tri des thématiques est laissé à la base
     * pour suivre sa collation : « Défense » vient avant « Économie », ce qu'un
     * tri PHP octet à octet placerait à l'envers.
     *
     * @return list<array{name: string, slug: string, decryptages: list<array<string, mixed>>}>
     */
    private function parCategorie(): array
    {
        $lignes = $this->connection->fetchAllAssociative(
            'SELECT * FROM (
                SELECT c.name AS categorie_name, c.slug AS categorie_slug,
                       dcr.title, dcr.legislature, dcr.vote_numero,
                       s.date_scrutin, s.sort_code, l.name AS lecture_name,
                       ROW_NUMBER() OVER (PARTITION BY c.id ORDER BY dcr.id DESC) AS rang
                FROM categorie c
                JOIN decryptage dcr ON dcr.categorie_id = c.id AND dcr.state = :published
                JOIN scrutin s ON s.id = dcr.scrutin_id
                LEFT JOIN lecture l ON l.id = dcr.lecture_id
             ) derniers
             WHERE rang <= ' . self::PAR_CATEGORIE . '
             ORDER BY categorie_name, rang',
            ['published' => self::PUBLIE],
        );

        $categories = [];

        foreach ($lignes as $ligne) {
            $slug = $ligne['categorie_slug'];
            $categories[$slug] ??= ['name' => $ligne['categorie_name'], 'slug' => $slug, 'decryptages' => []];
            $categories[$slug]['decryptages'][] = $ligne;
        }

        return array_values($categories);
    }

    /**
     * Tous les votes décryptés d'une thématique, du plus récemment publié au
     * plus ancien — l'ordre de publication de la rédaction, et non celui des
     * scrutins.
     *
     * @return list<array<string, mixed>>
     */
    private function decryptagesDeLaCategorie(int $categorie): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT ' . self::CARTE_SQL . '
             FROM decryptage dcr
             JOIN scrutin s ON s.id = dcr.scrutin_id
             JOIN categorie c ON c.id = dcr.categorie_id
             LEFT JOIN lecture l ON l.id = dcr.lecture_id
             WHERE dcr.state = :published AND dcr.categorie_id = :categorie
             ORDER BY dcr.id DESC',
            ['published' => self::PUBLIE, 'categorie' => $categorie],
        );
    }

    /**
     * Derniers scrutins tenus, décryptés ou non.
     *
     * @return list<array<string, mixed>>
     */
    private function derniersScrutins(int $limite): array
    {
        $scrutins = $this->connection->fetchAllAssociative(
            'SELECT ' . self::NUMERO_SQL . ' AS numero, s.legislature, s.sort_code, s.date_scrutin,
                    CONCAT(UPPER(LEFT(' . self::TITRE_SQL . ', 1)),
                           SUBSTRING(' . self::TITRE_SQL . ', 2)) AS titre
             FROM scrutin s
             ORDER BY s.date_scrutin DESC, s.numero DESC
             LIMIT ' . $limite,
        );

        foreach ($scrutins as &$scrutin) {
            $scrutin['titre'] = $this->motsLimites((string) $scrutin['titre'], self::MOTS_TITRE);
        }

        return $scrutins;
    }

    /** Port de word_limiter() : coupe au mot près et signale la coupure. */
    private function motsLimites(string $texte, int $mots): string
    {
        $morceaux = preg_split('/\s+/', trim($texte)) ?: [];

        if (\count($morceaux) <= $mots) {
            return $texte;
        }

        return implode(' ', \array_slice($morceaux, 0, $mots)) . ' ...';
    }

    /**
     * Années et mois pendant lesquels la législature a voté, pour le bloc
     * « Archives ».
     *
     * @return array<int, list<array{mois: int, libelle: string}>> indexé par année
     */
    private function archives(int $legislature): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT YEAR(date_scrutin) AS annee, MONTH(date_scrutin) AS mois
             FROM scrutin
             WHERE legislature = :legislature AND date_scrutin IS NOT NULL
             GROUP BY annee, mois
             ORDER BY annee, mois',
            ['legislature' => $legislature],
        );

        $archives = [];

        foreach ($rows as $row) {
            $mois = (int) $row['mois'];
            $archives[(int) $row['annee']][] = ['mois' => $mois, 'libelle' => self::MOIS[$mois]];
        }

        return $archives;
    }
}
