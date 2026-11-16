<?php

namespace App\Twig;

use App\Groupe\AdresseGroupe;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Filtre `adresse_groupe` : le segment d'adresse d'un groupe, à la place de
 * `libelle_abrev|lower` partout où le lien peut désigner un groupe disparu.
 *
 *     path('groupe_individual', {legislature: g.legislature, abrev: g.libelle_abrev|adresse_groupe(g.id)})
 *
 * L'argument est l'id ou l'uid du groupe ; sans lui, le filtre rend le sigle
 * en minuscules, ce qui suffit pour un groupe en activité. Cf. AdresseGroupe.
 */
class AdresseGroupeExtension extends AbstractExtension
{
    public function __construct(private readonly AdresseGroupe $adresses)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('adresse_groupe', fn (?string $sigle, int|string|null $groupe = null): string => $this->adresses->segment((string) $sigle, $groupe)),
        ];
    }
}
