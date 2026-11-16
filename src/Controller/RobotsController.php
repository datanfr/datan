<?php

namespace App\Controller;

use App\Referencement\Indexation;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * `/robots.txt` et `/llms.txt`, servis par l'application et non en fichier
 * statique.
 *
 * Le `public/robots.txt` du portage a été retiré au profit de ce contrôleur :
 * nginx sert les fichiers du dossier avant de passer la main à PHP
 * (`try_files $uri /index.php…`), et la préproduction n'avait donc **aucun
 * moyen** d'en servir un autre sans configurer le serveur à la main. Un seul
 * texte, versionné avec le code, qui s'adapte à l'hôte servi.
 *
 * Le coût du détour par PHP est nul : les deux réponses partent en cache
 * partagé, et le reverse proxy les ressert sans réexécuter le rendu.
 */
class RobotsController extends AbstractController
{
    /** Ces textes ne changent qu'au déploiement. */
    private const CACHE_TTL = 86400;

    public function __construct(private readonly Indexation $indexation)
    {
    }

    /**
     * Le fichier du site en production, à l'octet près ; celui de la
     * préproduction ferme tout.
     */
    #[Route('/robots.txt', name: 'robots_txt', methods: ['GET'])]
    public function robots(): Response
    {
        return $this->texte($this->indexation->estIndexable()
            ? 'referencement/robots.txt.twig'
            : 'referencement/robots_preproduction.txt.twig');
    }

    /**
     * Convention llmstxt.org, servie par la **seule** préproduction.
     *
     * datan.fr n'a pas ce fichier : lui en inventer un ajouterait au site une
     * page qu'il n'a pas, ce que la règle de parité interdit. Ici il a un rôle
     * précis — les moissonneurs de modèles de langue lisent `/llms.txt` avant
     * le reste, et c'est l'endroit où leur dire, en toutes lettres, que ce
     * domaine est une copie de travail et que la source est datan.fr.
     *
     * D'où le 404 en production : l'adresse n'y existe pas plus qu'avant.
     */
    #[Route('/llms.txt', name: 'llms_txt', methods: ['GET'])]
    public function llms(): Response
    {
        if ($this->indexation->estIndexable()) {
            throw $this->createNotFoundException();
        }

        return $this->texte('referencement/llms_preproduction.txt.twig');
    }

    /**
     * `text/plain` même pour `llms.txt`, que la convention veut en Markdown :
     * le type `text/markdown` fait proposer un téléchargement à la plupart des
     * navigateurs, là où le fichier est fait pour être lu à l'écran.
     */
    private function texte(string $gabarit): Response
    {
        $response = $this->render($gabarit);
        $response->headers->set('Content-Type', 'text/plain; charset=UTF-8');
        $response->setPublic();
        $response->setSharedMaxAge(self::CACHE_TTL);

        return $response;
    }
}
