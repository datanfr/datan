<?php

namespace App\Repository;

use App\Entity\Utilisateur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Utilisateur>
 */
class UtilisateurRepository extends ServiceEntityRepository implements PasswordUpgraderInterface, UserLoaderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Utilisateur::class);
    }

    /**
     * Connexion par identifiant **ou** e-mail, comme l'application d'origine
     * (`User_model::login`, qui tentait le pseudo puis l'e-mail). Le champ de
     * connexion annonce « Pseudo ou Email » : sans ce chargeur, l'e-mail ne
     * fonctionnerait pas et le libellé mentirait.
     *
     * L'e-mail n'a pas de contrainte d'unicité en base — deux comptes pourraient
     * le partager. On ne retient donc une correspondance par e-mail que si elle
     * est unique ; sinon on refuse, plutôt que de connecter l'un des deux au
     * hasard.
     */
    public function loadUserByIdentifier(string $identifiant): ?UserInterface
    {
        $parIdentifiant = $this->findOneBy(['identifiant' => $identifiant]);

        if ($parIdentifiant !== null) {
            return $parIdentifiant;
        }

        $parEmail = $this->findBy(['email' => $identifiant]);

        return \count($parEmail) === 1 ? $parEmail[0] : null;
    }

    /**
     * Réencode le mot de passe quand l'algorithme de hachage évolue.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Utilisateur) {
            throw new UnsupportedUserException(sprintf('Utilisateur non pris en charge : "%s".', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->flush();
    }
}
