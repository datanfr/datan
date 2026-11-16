<?php

namespace App\Repository;

use App\Entity\ListeEuropeenne;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListeEuropeenne>
 */
class ListeEuropeenneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeEuropeenne::class);
    }
}
