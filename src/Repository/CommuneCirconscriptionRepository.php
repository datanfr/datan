<?php

namespace App\Repository;

use App\Entity\CommuneCirconscription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommuneCirconscription>
 */
class CommuneCirconscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommuneCirconscription::class);
    }
}
