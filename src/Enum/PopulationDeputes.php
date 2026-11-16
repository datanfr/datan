<?php

namespace App\Enum;

/**
 * Population de députés qu'une page de liste présente pour une législature.
 *
 * L'application d'origine ne connaît que le couple `$active` / `$legislature`
 * pour distinguer ces trois cas, ce qui rend ses vues difficiles à lire ; on
 * nomme ici l'intention.
 */
enum PopulationDeputes: string
{
    /** Députés en exercice — la seule population qui a un sens sur la législature en cours. */
    case Actifs = 'actifs';

    /** Tous ceux qui ont siégé pendant la législature, législature achevée. */
    case Tous = 'tous';

    /** Députés dont le mandat s'est achevé avant la fin de la législature. */
    case Anciens = 'anciens';
}
