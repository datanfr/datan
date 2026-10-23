<?php

namespace App\Repository;

use App\Entity\DemandeCompteDepute;
use App\Entity\Depute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DemandeCompteDepute>
 */
class DemandeCompteDeputeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DemandeCompteDepute::class);
    }

    /**
     * La demande en attente d'un député, s'il en a une.
     *
     * C'est le verrou anti-abus « une demande par député » : tant qu'une demande
     * n'est pas traitée, une seconde saisie ne crée pas de doublon.
     */
    public function enAttentePourDepute(Depute $depute): ?DemandeCompteDepute
    {
        return $this->findOneBy(['depute' => $depute, 'etat' => DemandeCompteDepute::EN_ATTENTE]);
    }

    /**
     * La demande approuvée que porte un jeton d'activation, s'il est encore
     * valable. C'est ce que résout `/register/{token}` : approuvée (donc relue
     * par un administrateur) et non consommée (le jeton n'est pas remis à nul).
     */
    public function parToken(string $token): ?DemandeCompteDepute
    {
        return $this->findOneBy(['token' => $token, 'etat' => DemandeCompteDepute::APPROUVEE]);
    }

    /**
     * Les demandes à traiter, la plus ancienne d'abord.
     *
     * @return list<DemandeCompteDepute>
     */
    public function enAttente(): array
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.etat = :etat')
            ->setParameter('etat', DemandeCompteDepute::EN_ATTENTE)
            ->leftJoin('d.depute', 'depute')->addSelect('depute')
            ->orderBy('d.demandeeLe', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
