<?php

namespace App\Repository;

use App\Entity\Parrainage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Dépôt de {@see Parrainage}, réduit à ce que Doctrine exige pour le schéma et
 * l'import. La page `/parrainages-2022` lit en DBAL depuis son contrôleur, comme
 * le veut la convention des pages de lecture — aucune requête ici.
 *
 * @extends ServiceEntityRepository<Parrainage>
 */
class ParrainageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Parrainage::class);
    }
}
