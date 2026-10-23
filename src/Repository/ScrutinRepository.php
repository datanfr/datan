<?php

namespace App\Repository;

use App\Entity\Scrutin;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Scrutin>
 */
class ScrutinRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scrutin::class);
    }

    public function findOneByUid(string $uid): ?Scrutin
    {
        return $this->findOneBy(['uid' => $uid]);
    }
}
