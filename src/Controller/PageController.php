<?php

namespace App\Controller;

use App\AgeFrance;
use App\Http\RechauffeurCache;
use App\Legislature;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Pages éditoriales, portées du contrôleur Pages de l'application CodeIgniter
 * d'origine (`pages/view/{page}`, vues application/views/pages/).
 *
 * L'application d'origine les sert par une route fourre-tout `(:any)` placée en
 * dernier de son fichier de routes, qui cherche un fichier de vue portant le nom
 * demandé. Rien ne se cache derrière : il n'y a que quatre fichiers, et les
 * quatre sont ici. Un fourre-tout équivalent chez nous attraperait toute adresse
 * inconnue du site et rendrait un 404 plus tardif, sans rien apporter.
 *
 * `contact` fait exception : le contrôleur d'origine lui prépare bien un titre
 * et une description, mais le fichier de vue n'existe pas — l'adresse répond
 * 404 sur datan.fr. Il n'y a donc pas de page à porter.
 */
class PageController extends AbstractController
{
    /** Du texte fixe : rien ne le périme avant une réécriture de la rédaction. */
    private const CACHE_TTL = 86400;

    /**
     * La page de soutien affiche quatre compteurs, que la synchronisation
     * quotidienne fait bouger — et purge le cache après elle.
     */
    private const CACHE_TTL_CHIFFRES = RechauffeurCache::DUREE_PAGES;

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * Page d'explication des statistiques, cible de tous les liens « en savoir
     * plus » et des popovers du site.
     *
     * La priorité n'est pas décorative : `/statistiques/{page}` accepte
     * `[a-z\-]+`, donc `aide` aussi. Sans elle, cette page part en 404 dans
     * {@see ClassementController::individual()}, qui ne connaît que ses neuf
     * classements. L'application d'origine règle la même collision en déclarant
     * `statistiques/aide` avant `statistiques/(:any)` (routes.php:178).
     */
    #[Route('/statistiques/aide', name: 'page_statistiques', priority: 1, methods: ['GET'])]
    public function statistiques(): Response
    {
        return $this->page('page/statistiques.html.twig', [
            'title' => 'Les statistiques de Datan expliquées',
            'age_france_tous' => round(AgeFrance::TOUS),
            'age_france_eligibles' => round(AgeFrance::ELIGIBLES),
        ]);
    }

    #[Route('/a-propos', name: 'page_a_propos', methods: ['GET'])]
    public function aPropos(): Response
    {
        return $this->page('page/a-propos.html.twig', ['title' => 'À propos']);
    }

    #[Route('/mentions-legales', name: 'page_mentions_legales', methods: ['GET'])]
    public function mentionsLegales(): Response
    {
        return $this->page('page/mentions-legales.html.twig', ['title' => 'Mentions légales']);
    }

    #[Route('/soutenir', name: 'page_soutenir', methods: ['GET'])]
    public function soutenir(): Response
    {
        return $this->page(
            'page/soutenir.html.twig',
            ['title' => 'Soutenez-nous !'] + $this->chiffres(),
            self::CACHE_TTL_CHIFFRES,
        );
    }

    /**
     * Les quatre chiffres de la page de soutien.
     *
     * « Députés suivis » compte les députés ayant au moins un mandat, et non les
     * lignes de `depute` : l'import des acteurs en pose 995 de plus, qui n'ont
     * jamais siégé et n'ont donc ni fiche ni adresse. Les compter gonflerait
     * l'annonce d'un tiers. Le compte obtenu — 2 122 — est celui qu'affiche le
     * site, qui le tire de sa vue `deputes_last`.
     *
     * « Législatures » est écrit en dur sur le site ; il se déduit ici de
     * {@see Legislature}, pour qu'une législature ajoutée n'oblige pas à
     * retrouver le nombre au milieu d'un gabarit.
     *
     * @return array<string, int>
     */
    private function chiffres(): array
    {
        $chiffres = $this->connection->fetchAssociative(
            'SELECT (SELECT COUNT(DISTINCT depute_id) FROM mandat) AS deputes,
                    (SELECT COUNT(*) FROM decryptage WHERE state = :publie) AS decryptages,
                    (SELECT COUNT(*) FROM groupe
                      WHERE legislature = :legislature
                        AND date_fin IS NULL
                        AND libelle_abrev <> :non_inscrits) AS groupes',
            [
                'publie' => 'published',
                'legislature' => Legislature::COURANTE,
                // Les non-inscrits ne forment pas un groupe : le site les écarte
                // de tous ses décomptes de groupes.
                'non_inscrits' => 'NI',
            ],
        ) ?: [];

        return array_map(intval(...), $chiffres) + ['legislatures' => \count(Legislature::publiees())];
    }

    /**
     * @param array<string, mixed> $parametres
     */
    private function page(string $gabarit, array $parametres, int $ttl = self::CACHE_TTL): Response
    {
        // Fil d'Ariane des pages éditoriales : Datan puis le titre, l'adresse du
        // dernier maillon étant celle de la page elle-même (Pages.php:52).
        $requete = $this->container->get('request_stack')->getCurrentRequest();
        $parametres['fil_ariane'] = [
            ['nom' => 'Datan', 'url' => $this->generateUrl('home')],
            ['nom' => $parametres['title'], 'url' => $requete?->getPathInfo() ?? '/'],
        ];

        $response = $this->render($gabarit, $parametres);

        $response->setPublic();
        $response->setSharedMaxAge($ttl);

        return $response;
    }
}
