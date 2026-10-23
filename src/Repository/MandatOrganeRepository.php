<?php

namespace App\Repository;

use App\Entity\MandatOrgane;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MandatOrgane>
 */
class MandatOrganeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MandatOrgane::class);
    }
}
