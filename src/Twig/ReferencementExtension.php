<?php

namespace App\Twig;

use App\Referencement\Indexation;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * `site_indexable()` : ce que les gabarits mettent dans `<meta name="robots">`.
 *
 * Une fonction, et non une variable globale de `twig.yaml` comme `matomo_url` :
 * la réponse dépend de l'hôte de la requête en cours, qu'une globale — résolue
 * à la configuration, hors contexte HTTP — ne connaît pas.
 *
 * Séparé de {@see DatanExtension}, qui ne porte que les helpers d'affichage
 * repris de l'application d'origine.
 */
final class ReferencementExtension extends AbstractExtension
{
    public function __construct(private readonly Indexation $indexation)
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('site_indexable', $this->indexation->estIndexable(...)),
        ];
    }
}
