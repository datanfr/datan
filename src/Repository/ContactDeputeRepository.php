<?php

namespace App\Repository;

use App\Entity\ContactDepute;
use App\Entity\Depute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactDepute>
 */
class ContactDeputeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactDepute::class);
    }

    /**
     * Fiche de contact d'un député, ou une fiche neuve rattachée à lui si aucune
     * n'existe encore — l'écran d'édition doit pouvoir renseigner un député
     * jamais saisi.
     */
    public function pourDepute(Depute $depute): ContactDepute
    {
        return $this->findOneBy(['depute' => $depute])
            ?? (new ContactDepute())->setDepute($depute);
    }
}
