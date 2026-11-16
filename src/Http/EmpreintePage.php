<?php

namespace App\Http;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Donne un `ETag` à toute page publique : c'est le cache du navigateur, pour
 * une page.
 *
 * Une page n'annonce au navigateur aucune durée (`s-maxage` ne vaut que pour un
 * cache partagé), et c'est voulu : sa copie ne se purge pas. Avec une durée,
 * un visiteur garderait la page de la veille après la synchronisation, un
 * rédacteur ne verrait pas ce qu'il vient de publier, et qui se connecte
 * retrouverait la navbar anonyme. Un fichier de `public/assets/` peut être
 * gardé un an parce que son adresse change avec son contenu ; l'adresse d'une
 * page, elle, est un contrat.
 *
 * Ce qu'on peut offrir sans rien risquer, c'est la revalidation : le
 * navigateur — ou un robot d'indexation — redemande la page avec
 * `If-None-Match`, et le reverse proxy répond `304` sans corps tant que
 * l'empreinte en magasin est la même. On économise le transfert, pas
 * l'aller-retour.
 *
 * L'empreinte est posée avant la mise en magasin, qui la conserve avec la
 * réponse : le `304` sort alors de {@see CacheAvecSession} sans réveiller
 * l'application.
 */
#[AsEventListener(event: KernelEvents::RESPONSE)]
final class EmpreintePage
{
    public function __invoke(ResponseEvent $event): void
    {
        $reponse = $event->getResponse();

        if (!$event->isMainRequest()
            || !$event->getRequest()->isMethodCacheable()
            || $reponse->getStatusCode() !== Response::HTTP_OK
            || !$reponse->headers->hasCacheControlDirective('public')
            || $reponse->headers->has('ETag')
        ) {
            return;
        }

        // Une réponse en flux ou un fichier n'ont pas de contenu à lire ici.
        $contenu = $reponse->getContent();

        if (\is_string($contenu) && $contenu !== '') {
            $reponse->setEtag(hash('xxh128', $contenu));
        }
    }
}
