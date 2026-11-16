<?php

namespace App\Http;

/**
 * Demande de réchauffage du cache HTTP, posée à la publication d'un contenu
 * et traitée par {@see RechauffeurCache::traiterDemande()} sur le transport
 * asynchrone.
 */
final class RechauffageDemande
{
    public function __construct(
        /** Horodatage Unix (secondes, avec fraction) de la demande. */
        public readonly float $demandeeLe,
    ) {
    }
}
