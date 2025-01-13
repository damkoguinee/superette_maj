<?php

namespace App\Repository;

use App\Entity\Dimensions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dimensions>
 *
 * @method Dimensions|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dimensions|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dimensions[]    findAll()
 * @method Dimensions[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DimensionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dimensions::class);
    }

//    /**
//     * @return Dimensions[] Returns an array of Dimensions objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Dimensions
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
