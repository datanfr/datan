<?php

namespace App\Enum;

/**
 * État d'un décryptage de vote (couche éditoriale votes_datan de l'app d'origine).
 */
enum DecryptageState: string
{
    case Draft = 'draft';
    case Published = 'published';
}
