<?php

namespace App\Tests\Http;

use App\Http\MagasinDouble;
use App\Http\RechauffeurCache;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class MagasinDoubleTest extends TestCase
{
    private string $racine;

    protected function setUp(): void
    {
        $this->racine = sys_get_temp_dir() . '/datan-magasin-' . bin2hex(random_bytes(4));
        mkdir($this->racine);
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->racine);
    }

    public function testUnePageFigeeSurvitALaPurgeDuMagasinVivant(): void
    {
        $magasin = MagasinDouble::sous($this->racine);
        $vivante = Request::create('https://datan.fr/deputes');
        $figee = Request::create('https://datan.fr/votes/legislature-15/vote_1');

        $magasin->write($vivante, $this->page('députés', RechauffeurCache::DUREE_PAGES));
        $magasin->write($figee, $this->page('scrutin de 2017', RechauffeurCache::DUREE_FIGEE));

        self::assertSame('députés', $this->contenu($magasin->lookup($vivante)));
        self::assertSame('scrutin de 2017', $this->contenu($magasin->lookup($figee)));

        // La purge ordinaire : le dossier des pages vivantes disparaît.
        (new Filesystem())->remove($this->racine . '/' . MagasinDouble::VIVANT);
        $magasin = MagasinDouble::sous($this->racine);

        self::assertNull($magasin->lookup($vivante));
        self::assertSame('scrutin de 2017', $this->contenu($magasin->lookup($figee)));
    }

    public function testLaDureeDeLaReponseChoisitLeMagasin(): void
    {
        self::assertFalse(MagasinDouble::estFigee($this->page('', RechauffeurCache::DUREE_PAGES)));
        // Les trois jours d'un scrutin de la législature en cours ne suffisent pas.
        self::assertFalse(MagasinDouble::estFigee($this->page('', 259200)));
        self::assertTrue(MagasinDouble::estFigee($this->page('', RechauffeurCache::DUREE_FIGEE)));
        self::assertFalse(MagasinDouble::estFigee(new Response('privée')));
    }

    /**
     * Un ancien député qui retrouve un siège : sa page figée est encore en
     * magasin, le réchauffage en écrit une vivante. C'est elle qui doit sortir.
     */
    public function testLaPageVivantePrimeSurUneFigeeRestee(): void
    {
        $magasin = MagasinDouble::sous($this->racine);
        $requete = Request::create('https://datan.fr/deputes/var-83/depute_untel');

        $magasin->write($requete, $this->page('ancien député', RechauffeurCache::DUREE_FIGEE));
        $magasin->write($requete, $this->page('député en exercice', RechauffeurCache::DUREE_PAGES));

        self::assertSame('député en exercice', $this->contenu($magasin->lookup($requete)));
    }

    public function testPurgerUneAdresseLaRetireDesDeuxMagasins(): void
    {
        $magasin = MagasinDouble::sous($this->racine);
        $requete = Request::create('https://datan.fr/elections/legislatives-2022');

        $magasin->write($requete, $this->page('résultats', RechauffeurCache::DUREE_FIGEE));

        self::assertTrue($magasin->purge('https://datan.fr/elections/legislatives-2022'));
        self::assertNull($magasin->lookup($requete));
    }

    /**
     * Le magasin rend la réponse sans son corps, avec le chemin du fichier qui
     * le porte : c'est le reverse proxy qui le relit ensuite.
     */
    private function contenu(?Response $reponse): ?string
    {
        if ($reponse === null) {
            return null;
        }

        return (string) file_get_contents((string) $reponse->headers->get('X-Body-File'));
    }

    private function page(string $contenu, int $duree): Response
    {
        $reponse = new Response($contenu);
        $reponse->setPublic();
        $reponse->setSharedMaxAge($duree);

        return $reponse;
    }
}
