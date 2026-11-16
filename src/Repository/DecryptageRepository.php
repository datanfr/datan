<?php

namespace App\Repository;

use App\Entity\Decryptage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Decryptage>
 */
class DecryptageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Decryptage::class);
    }

    public function findOneByVote(int $legislature, int $voteNumero): ?Decryptage
    {
        return $this->findOneBy(['legislature' => $legislature, 'voteNumero' => $voteNumero]);
    }
}
