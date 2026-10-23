<?php

namespace App\Controller;

use App\Commune\SeuilCommunes;
use App\CouleurGroupe;
use App\Http\RechauffeurCache;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Départements et collectivités d'élection (Departement::liste et
 * Departement::view de l'application CodeIgniter d'origine, vues
 * application/views/departement/).
 */
class DepartementController extends AbstractController
{
    /** Le découpage ne bouge qu'aux élections. */
    private const CACHE_TTL = RechauffeurCache::DUREE_PAGES;

    /**
     * Seuil de population du bloc « Communes les plus peuplées »
     * (url_obf_cities()). En deçà, la commune a bien une fiche mais aucun lien
     * en clair n'y mène — seulement des liens masqués aux robots
     * ({@see \App\Twig\DatanExtension::urlObf()}) : {@see SitemapController}
     * n'annonce donc pas ces adresses.
     */
    public const POPULATION_MINIMALE = 4000;

    /**
     * Collectivité sans découpage communal, pour laquelle l'application
     * d'origine masque le bloc des communes : les Français de l'étranger.
     *
     * Saint-Pierre-et-Miquelon en faisait partie jusqu'au 6 septembre 2026 :
     * ses deux communes n'ayant pas de population au référentiel, le bloc
     * aurait été vide. Le site les liste désormais hors seuil
     * ({@see SeuilCommunes}) — sur sa page de département seulement, sa fiche
     * de ville ayant gardé l'ancien masquage par oubli (le commit 79e5795f ne
     * touche que `departement/index.php`). Le partiel étant commun aux deux
     * pages, la règle corrigée vaut ici pour les deux.
     */
    public const SANS_COMMUNES = ['099'];

    /**
     * `/deputes/…` sert aussi les listes par législature et les anciens députés,
     * servies par {@see DeputeListController} : rien ne garantit l'ordre entre
     * deux contrôleurs, d'où ces exclusions explicites. Le motif reste large
     * parce que les slugs de l'application d'origine n'ont pas tous la même
     * forme — `ain-01` mais `francais-de-letranger`.
     *
     * Partagé avec {@see CommuneController}, dont les adresses commencent par le
     * même segment.
     *
     * **Les majuscules sont acceptées, puis redirigées.** Le routeur de
     * l'application d'origine ne regarde pas la casse et sa base non plus : le
     * site sert donc la même page sous `corse-du-sud-2a`, `corse-du-sud-2A` et
     * `NORD-59`, toutes en 200. Refuser ces formes rendrait 404 là où le site
     * répond ; les servir en 200 dupliquerait chaque page sous une infinité
     * d'adresses. D'où la troisième voie, celle des balises canoniques déjà
     * corrigées ailleurs : on accepte, et on renvoie en 301 vers la forme de
     * `departement.slug` ({@see self::versLaCasseCanonique()}). Les exclusions
     * deviennent insensibles à la casse pour la même raison — sans quoi
     * `/deputes/Legislature-16` échapperait à celle qui protège
     * {@see DeputeListController}.
     */
    public const SLUG = '(?!(?i:legislature-))(?!(?i:inactifs$))[a-zA-Z0-9\-]+';

    /** Un député n'est de ce département que s'il y siège encore. */
    private const EN_EXERCICE = 'EXISTS (SELECT 1 FROM mandat m
                                         WHERE m.depute_id = d.id
                                           AND m.legislature = :legislature
                                           AND m.date_fin IS NULL)';

    public function __construct(private readonly Connection $connection)
    {
    }

    #[Route('/index_departements', name: 'departements_index', methods: ['GET'])]
    public function index(): Response
    {
        $response = $this->render('departement/all.html.twig', [
            // Tri de l'application d'origine : `ORDER BY departement_code` sur
            // une colonne de texte. « 099 » (Français de l'étranger) tombe donc
            // entre l'Ariège et l'Aube, et la Corse après le Val-d'Oise.
            'departements' => $this->connection->fetchAllAssociative(
                'SELECT code, nom, slug FROM departement ORDER BY code',
            ),
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Tous les départements', 'url' => $this->generateUrl('departements_index')],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    #[Route('/deputes/{departement}', name: 'departement_individual', requirements: ['departement' => self::SLUG], methods: ['GET'])]
    public function individual(string $departement): Response
    {
        $ligne = $this->connection->fetchAssociative(
            'SELECT id, code, nom, slug, libelle_de, region FROM departement WHERE slug = :slug',
            ['slug' => $departement],
        );

        if ($ligne === false) {
            return $this->versLAdresseCanonique($departement);
        }

        // La collation de MariaDB ignore la casse : la requête ci-dessus a donc
        // pu répondre à `Corse-du-Sud-2A`. C'est ici, et non dans le motif de
        // route, que la forme non canonique se rattrape.
        if ($departement !== $ligne['slug']) {
            return $this->redirectToRoute(
                'departement_individual',
                ['departement' => $ligne['slug']],
                Response::HTTP_MOVED_PERMANENTLY,
            );
        }

        $deputes = $this->deputes((string) $ligne['code']);

        // L'application d'origine rend un 404 sur un département sans député en
        // exercice, et non une page vide.
        if ($deputes === []) {
            throw $this->createNotFoundException('Département sans député en exercice.');
        }

        $response = $this->render('departement/individual.html.twig', [
            'legislature' => Legislature::COURANTE,
            'departement' => $ligne,
            'deputes' => $deputes,
            'communes' => \in_array($ligne['code'], self::SANS_COMMUNES, true)
                ? []
                : $this->communes((int) $ligne['id'], (string) $ligne['slug']),
            'fil_ariane' => [
                ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
                ['nom' => 'Députés', 'url' => $this->generateUrl('deputes_index')],
                [
                    'nom' => $ligne['nom'] . ' (' . $ligne['code'] . ')',
                    'url' => $this->generateUrl('departement_individual', ['departement' => $ligne['slug']]),
                ],
            ],
        ]);

        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }

    /**
     * Filet pour un `dpt_slug` fabriqué (repli) qui ne serait pas l'adresse du
     * site : on renvoie vers `departement.slug`, la table tenue à la main de
     * l'application d'origine, plutôt que de rendre un 404.
     *
     * Les cinq divergences historiques (`francais-de-letranger` et non
     * `francais-etablis-hors-de-france-099`, `cote-dor-21` et non `cote-d-or-21`,
     * etc.) sont désormais résorbées **à la source** : `ImportMandatsCommand`
     * dérive `dpt_slug` de `departement.slug`, si bien que ces anciennes formes
     * fabriquées ne sont plus produites nulle part et répondent 404 — comme sur
     * datan.fr, qui ne les a jamais servies. Ce filet ne joue plus que pour le
     * repli de fabrication (un département absent de la table du legacy).
     */
    private function versLAdresseCanonique(string $slug): Response
    {
        $canonique = $this->connection->fetchOne(
            'SELECT dep.slug
             FROM depute d
             JOIN departement dep ON dep.code = d.departement_code
             WHERE d.dpt_slug = :slug
             LIMIT 1',
            ['slug' => $slug],
        );

        if ($canonique === false) {
            throw $this->createNotFoundException('Département inconnu.');
        }

        return $this->redirectToRoute(
            'departement_individual',
            ['departement' => $canonique],
            Response::HTTP_MOVED_PERMANENTLY,
        );
    }

    /**
     * Députés en exercice élus dans le département.
     *
     * Rangés par nom et non par circonscription : c'est l'ordre de
     * l'application d'origine (`ORDER BY nameLast, nameFirst`), et donc celui
     * des cartes du site.
     *
     * `civilite` ne sert qu'au titre de la page, qui s'accorde quand le
     * département n'a qu'un siège.
     *
     * @return list<array<string, mixed>>
     */
    private function deputes(string $code): array
    {
        return $this->connection->fetchAllAssociative(
            'SELECT d.mp_id, d.firstname, d.lastname, d.slug, d.dpt_slug,
                    d.departement_nom, d.departement_code, d.circonscription, d.civilite,
                    g.legislature, g.libelle AS groupe_libelle, g.libelle_abrev AS groupe_abrev,
                    ' . CouleurGroupe::SQL . ' AS groupe_couleur
             FROM depute d
             LEFT JOIN groupe g ON g.id = d.groupe_id
             WHERE d.departement_code = :code AND ' . self::EN_EXERCICE . '
             ORDER BY d.lastname, d.firstname',
            ['code' => $code, 'legislature' => Legislature::COURANTE],
        );
    }

    /**
     * Communes du département dépassant le seuil de population, de la plus
     * peuplée à la plus petite — ou, pour une collectivité sans population au
     * référentiel, ses vingt premières communes ({@see SeuilCommunes}).
     *
     * @return list<array<string, mixed>>
     */
    private function communes(int $departement, string $slug): array
    {
        $limite = SeuilCommunes::limite($slug, null);

        return $this->connection->fetchAllAssociative(
            'SELECT nom, slug, population
             FROM commune
             WHERE departement_id = :departement AND ' . SeuilCommunes::condition() . '
             ORDER BY population DESC, nom'
            . ($limite === null ? '' : ' LIMIT ' . $limite),
            ['departement' => $departement] + SeuilCommunes::parametres($slug, self::POPULATION_MINIMALE),
        );
    }
}
