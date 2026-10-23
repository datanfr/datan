<?php

namespace App\Referencement;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * `X-Robots-Tag: noindex` sur **toute** réponse d'un hôte non indexable.
 *
 * La balise `<meta name="robots">` ne couvre que le HTML que nos gabarits
 * rendent. L'en-tête, lui, vaut pour tout ce qui sort : les quatorze plans de
 * site XML, les collections `/api`, une page d'erreur, et le HTML qu'un gabarit
 * futur oublierait de baliser. La balise est la ceinture, l'en-tête les
 * bretelles — et c'est l'en-tête qui tient.
 *
 * `noarchive` en plus des deux directives demandées : sans lui, un moteur qui a
 * déjà vu l'adresse peut continuer d'en afficher une copie en cache.
 *
 * Compatible avec le reverse proxy intégré : l'en-tête est posé avant la mise
 * en magasin, et la clé de cache de `Store` porte l'URI complète, hôte compris
 * — une réponse de préproduction ne peut pas être resservie pour datan.fr.
 */
#[AsEventListener(event: KernelEvents::RESPONSE)]
final class EnTeteRobots
{
    public function __construct(private readonly Indexation $indexation)
    {
    }

    public function __invoke(ResponseEvent $event): void
    {
        // Les sous-requêtes sont absorbées par la principale, qui porte déjà
        // l'en-tête : les traiter ne ferait que dupliquer le travail.
        if (!$event->isMainRequest() || $this->indexation->estIndexable()) {
            return;
        }

        $event->getResponse()->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }
}
