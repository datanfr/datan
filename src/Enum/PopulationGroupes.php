<?php

namespace App\Enum;

/**
 * Population de groupes parlementaires qu'une page de liste présente.
 *
 * Pendant qu'une législature court, un groupe peut être dissous ou se
 * reconstituer sous un autre nom : la liste des groupes en activité et celle
 * des groupes dissous coexistent donc pour une même législature.
 */
enum PopulationGroupes: string
{
    /** Groupes en activité — la page principale d'une législature en cours. */
    case Actifs = 'actifs';

    /** Groupes dissous avant la fin de la législature. */
    case Dissous = 'dissous';

    /** Tous les groupes d'une législature achevée, dissous ou non. */
    case Tous = 'tous';
}
