<?php

namespace App\Repository;

use App\Entity\PartielleLegislative;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PartielleLegislative>
 */
class PartielleLegislativeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PartielleLegislative::class);
    }
}
