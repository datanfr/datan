<?php

namespace App;

/**
 * Âge moyen de la population française, repris des helpers `mean_age_france()`
 * et `mean_age_france_all()` de l'application CodeIgniter d'origine.
 *
 * Ce sont des constantes et non des mesures : elles viennent d'un calcul manuel
 * sur la pyramide des âges de l'Insee, refait à la main quand la rédaction le
 * décide. La page `/statistiques/aide` en publie la méthode et le tableur.
 */
final class AgeFrance
{
    /** Toute la population, mineurs compris. */
    public const TOUS = 42.1;

    /**
     * Français en âge d'être élus, c'est-à-dire de plus de 18 ans. C'est à
     * cette moyenne-là que les classements comparent l'âge des députés : la
     * comparer à celle de toute la population reviendrait à opposer une
     * assemblée d'adultes à un pays qui compte ses enfants.
     */
    public const ELIGIBLES = 50.52;
}
