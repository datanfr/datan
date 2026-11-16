<?php

namespace App\Tests\Http;

use App\Http\EmpreintePage;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class EmpreintePageTest extends TestCase
{
    public function testUnePagePubliqueRecoitUneEmpreinteQuiSuitSonContenu(): void
    {
        $premiere = $this->traiter($this->pagePublique('<h1>Scrutin</h1>'));
        $identique = $this->traiter($this->pagePublique('<h1>Scrutin</h1>'));
        $modifiee = $this->traiter($this->pagePublique('<h1>Scrutin décrypté</h1>'));

        self::assertNotNull($premiere->getEtag());
        self::assertSame($premiere->getEtag(), $identique->getEtag());
        self::assertNotSame($premiere->getEtag(), $modifiee->getEtag());
    }

    /**
     * C'est l'effet recherché : le navigateur qui présente l'empreinte reçoit
     * un 304 sans corps.
     */
    public function testLaMemeEmpreinteVautUn304(): void
    {
        $page = $this->traiter($this->pagePublique('<h1>Scrutin</h1>'));

        $requete = Request::create('/votes/legislature-17/vote_1');
        $requete->headers->set('If-None-Match', (string) $page->getEtag());

        self::assertTrue($page->isNotModified($requete));
        self::assertSame(Response::HTTP_NOT_MODIFIED, $page->getStatusCode());
    }

    public function testUnePagePriveeNEnRecoitPas(): void
    {
        self::assertNull($this->traiter(new Response('Mon compte'))->getEtag());
    }

    public function testUneErreurOuUneEcritureNEnRecoiventPas(): void
    {
        $erreur = $this->pagePublique('Introuvable');
        $erreur->setStatusCode(Response::HTTP_NOT_FOUND);

        self::assertNull($this->traiter($erreur)->getEtag());
        self::assertNull($this->traiter($this->pagePublique('Enregistré'), 'POST')->getEtag());
    }

    public function testUneEmpreinteDejaPoseeEstRespectee(): void
    {
        $page = $this->pagePublique('<h1>Scrutin</h1>');
        $page->setEtag('choisie-par-le-controleur');

        self::assertSame('"choisie-par-le-controleur"', $this->traiter($page)->getEtag());
    }

    private function pagePublique(string $contenu): Response
    {
        $reponse = new Response($contenu);
        $reponse->setPublic();
        $reponse->setSharedMaxAge(86400);

        return $reponse;
    }

    private function traiter(Response $reponse, string $methode = 'GET'): Response
    {
        (new EmpreintePage())(new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/votes/legislature-17/vote_1', $methode),
            HttpKernelInterface::MAIN_REQUEST,
            $reponse,
        ));

        return $reponse;
    }
}
