<?php

namespace App\Enum;

/**
 * Position d'un député lors d'un scrutin, alignée sur le décompte nominatif
 * du modèle open-data de l'Assemblée nationale (pour / contre / abstention / non-votant).
 */
enum VotePosition: string
{
    case Pour = 'pour';
    case Contre = 'contre';
    case Abstention = 'abstention';
    case NonVotant = 'nonVotant';
}
