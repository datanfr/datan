<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Les redirections 301 que l'application d'origine porte dans son .htaccess
 * (.htaccess.dist:24-60) — pas dans routes.php. Ce sont les adresses d'avant
 * la mise en législatures du site (2022) : indexées et partagées depuis des
 * années, elles font partie du contrat d'URL au même titre que les vivantes.
 *
 * Deux règles du .htaccess ne sont pas reprises ici : la suppression du slash
 * final (Symfony la fait nativement, en 301 aussi) et le hook urlValidator
 * (doubles slashes → 404 : aucune de nos routes ne les accepte, le 404 tombe
 * de lui-même).
 */
class RedirectionLegacyController extends AbstractController
{
    /**
     * Les groupes de la 15e législature, seuls concernés : leurs adresses
     * courtes datent d'avant les préfixes `legislature-N`. L'énumération sert
     * de contrainte de route — sans elle, `/groupes/{slug}` avalerait les
     * adresses `legislature-16` déclarées dans GroupeController (l'ordre entre
     * deux contrôleurs n'est jamais garanti, cf. CLAUDE.md).
     */
    private const GROUPES_L15 = 'larem|lr|dem|soc|agir-e|ni|udi_i|lt|fi|gdr|eds|lc|modem|ng|udi-i|udi-i-a|udi-agir';

    #[Route('/votes/vote_{numero}', requirements: ['numero' => '\d{1,4}'], methods: ['GET'])]
    public function vote(int $numero): Response
    {
        return $this->redirectToRoute('vote_individual', ['legislature' => 15, 'numero' => $numero], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/votes/all', methods: ['GET'])]
    public function votesTous(): Response
    {
        return $this->redirectToRoute('votes_legislature', ['legislature' => 15], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/votes/all/{annee}', requirements: ['annee' => '\d{4}'], methods: ['GET'])]
    public function votesAnnee(int $annee): Response
    {
        return $this->redirectToRoute('votes_annee', ['legislature' => 15, 'annee' => $annee], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/votes/all/{annee}/{mois}', requirements: ['annee' => '\d{4}', 'mois' => '\d{1,2}'], methods: ['GET'])]
    public function votesMois(int $annee, string $mois): Response
    {
        return $this->redirectToRoute('votes_mois', ['legislature' => 15, 'annee' => $annee, 'mois' => $mois], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/groupes/{abrev}', requirements: ['abrev' => self::GROUPES_L15], methods: ['GET'])]
    public function groupe(string $abrev): Response
    {
        return $this->redirectToRoute('groupe_individual', ['legislature' => 15, 'abrev' => $abrev], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Sous-pages des adresses courtes de groupe (`/groupes/soc/membres`…) :
     * le .htaccess recopie le reste du chemin tel quel, on fait pareil — la
     * cible tranche elle-même entre 200 et 404.
     */
    #[Route('/groupes/{abrev}/{reste}', requirements: ['abrev' => self::GROUPES_L15, 'reste' => '.+'], methods: ['GET'])]
    public function groupeSousPage(string $abrev, string $reste): Response
    {
        return $this->redirect('/groupes/legislature-15/' . $abrev . '/' . $reste, Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Trois députés dont l'adresse a changé : Eva Sas élue à Paris après
     * l'Essonne, Benjamin Lucas devenu Lucas-Lundy, Caroline Yadan passée aux
     * Français de l'étranger. Le .htaccess les redirige un à un, à la main.
     *
     * `priority: 2` : sans elle, `depute_individual` — dont le motif couvre
     * ces trois adresses — peut être servie d'abord (l'ordre entre deux
     * contrôleurs n'est jamais garanti), et sa redirection canonique
     * s'appliquerait à la place de celle-ci, vers `depute.dpt_slug` — le slug
     * fabriqué, qui pour les Français de l'étranger n'est pas l'adresse du
     * site (cf. CLAUDE.md, « deux jeux de slugs »).
     */
    #[Route('/deputes/essonne-91/depute_eva-sas', methods: ['GET'], priority: 2)]
    public function evaSas(): Response
    {
        return $this->redirectToRoute('depute_individual', ['dptSlug' => 'paris-75', 'slug' => 'eva-sas'], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/deputes/yvelines-78/depute_benjamin-lucas', methods: ['GET'], priority: 2)]
    public function benjaminLucas(): Response
    {
        return $this->redirectToRoute('depute_individual', ['dptSlug' => 'yvelines-78', 'slug' => 'benjamin-lucaslundy'], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/deputes/paris-75/depute_caroline-yadan', methods: ['GET'], priority: 2)]
    public function carolineYadan(): Response
    {
        return $this->redirectToRoute('depute_individual', ['dptSlug' => 'francais-de-letranger', 'slug' => 'caroline-yadan'], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Les anciennes pages de votes filtrées d'un député (`/votes/all`, puis
     * `/votes/<champ>` — un tri, une position) ramènent toutes à la liste.
     * Le lookahead reproduit le `RewriteCond !votes/all` : une adresse
     * profonde contenant `all` reste hors de la règle et rend 404, comme sur
     * le site vivant.
     */
    #[Route('/deputes/{dptSlug}/depute_{slug}/votes/{champ}', requirements: ['dptSlug' => '[a-z0-9\-]+', 'slug' => '[a-z0-9\-]+', 'champ' => '(?!all(/|$)).+|all'], methods: ['GET'])]
    public function votesDepute(string $dptSlug, string $slug): Response
    {
        return $this->redirectToRoute('depute_votes', ['dptSlug' => $dptSlug, 'slug' => $slug], Response::HTTP_MOVED_PERMANENTLY);
    }

    /**
     * Même règle pour les groupes — mais `/votes/all` y est une vraie page
     * (groupe_votes_tous) : le lookahead l'écarte, seule la variante filtrée
     * redirige.
     */
    #[Route('/groupes/legislature-{legislature}/{abrev}/votes/{champ}', requirements: ['legislature' => '\d+', 'abrev' => '[a-z0-9._\-\&]+', 'champ' => '(?!all(/|$)).+'], methods: ['GET'])]
    public function votesGroupe(int $legislature, string $abrev): Response
    {
        return $this->redirectToRoute('groupe_votes', ['legislature' => $legislature, 'abrev' => $abrev], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/dashboard-mp', methods: ['GET'])]
    public function dashboardMp(): Response
    {
        return $this->redirectToRoute('dashboard', [], Response::HTTP_MOVED_PERMANENTLY);
    }

    #[Route('/dashboard-mp/{reste}', requirements: ['reste' => '.+'], methods: ['GET'])]
    public function dashboardMpSousPage(string $reste): Response
    {
        return $this->redirect('/dashboard/' . $reste, Response::HTTP_MOVED_PERMANENTLY);
    }
}
