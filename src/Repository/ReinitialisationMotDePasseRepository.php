<?php

namespace App\Repository;

use App\Entity\ReinitialisationMotDePasse;
use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReinitialisationMotDePasse>
 */
class ReinitialisationMotDePasseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReinitialisationMotDePasse::class);
    }

    public function parToken(string $token): ?ReinitialisationMotDePasse
    {
        return $this->findOneBy(['token' => $token]);
    }

    /**
     * Purge les jetons pendants d'un compte avant d'en émettre un neuf : une
     * seule demande vaut à la fois, et le lien précédent cesse aussitôt.
     */
    public function purgerPour(Utilisateur $utilisateur): void
    {
        $this->createQueryBuilder('r')
            ->delete()
            ->where('r.utilisateur = :utilisateur')
            ->setParameter('utilisateur', $utilisateur)
            ->getQuery()
            ->execute();
    }
}
