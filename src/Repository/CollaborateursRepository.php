<?php

namespace App\Repository;

use App\Entity\Collaborateurs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Collaborateurs>
 *
 * @method Collaborateurs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Collaborateurs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Collaborateurs[]    findAll()
 * @method Collaborateurs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CollaborateursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Collaborateurs::class);
    }

//    /**
//     * @return Collaborateurs[] Returns an array of Collaborateurs objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Collaborateurs
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
