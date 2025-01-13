<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
* @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findUserSearchByLieu($value, $lieu): array
    {
        $type = "developpeur";
        return $this->createQueryBuilder('u')
            ->where('u.typeUser != :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('u.lieuVente = :lieu')
            ->andWhere('u.prenom LIKE :val Or u.nom LIKE :val Or u.telephone LIKE :val')
            ->setParameter('type', $type)
            ->setParameter('statut', 'actif')
            ->setParameter('lieu', $lieu)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function findClientSearchByLieu($value, $lieu): array
    {
        $type = "client";
        return $this->createQueryBuilder('u')
            ->where('u.typeUser = :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('u.lieuVente = :lieu')
            ->andWhere('u.prenom LIKE :val Or u.nom LIKE :val Or u.telephone LIKE :val')
            ->setParameter('type', $type)
            ->setParameter('statut', 'actif')
            ->setParameter('lieu', $lieu)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function findClientInactifSearchByLieu($value, $lieu): array
    {
        $type = "client";
        return $this->createQueryBuilder('u')
            ->where('u.typeUser = :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('u.lieuVente = :lieu')
            ->andWhere('u.prenom LIKE :val Or u.nom LIKE :val Or u.telephone LIKE :val')
            ->setParameter('type', $type)
            ->setParameter('statut', 'inactif')
            ->setParameter('lieu', $lieu)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function findAllClientSearch($value): array
    {
        $type = "client";
        return $this->createQueryBuilder('u')
            ->where('u.typeUser = :type')
            ->andWhere('u.prenom LIKE :val Or u.nom LIKE :val Or u.telephone LIKE :val')
            ->setParameter('type', $type)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function findPersonnelSearchByLieu($value, $lieu): array
    {
        $type = "personnel";
        return $this->createQueryBuilder('u')
            ->where('u.typeUser = :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('u.lieuVente = :lieu')
            ->andWhere('u.prenom LIKE :val Or u.nom LIKE :val Or u.telephone LIKE :val')
            ->setParameter('type', $type)
            ->setParameter('statut', 'actif')
            ->setParameter('lieu', $lieu)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }

    public function findMaxId(): ?int
    {
        $result = $this->createQueryBuilder('u')
            ->select('MAX(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    
}
