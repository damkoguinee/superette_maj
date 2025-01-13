<?php

namespace App\Repository;

use App\Entity\OrigineProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrigineProduit>
 *
 * @method OrigineProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrigineProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrigineProduit[]    findAll()
 * @method OrigineProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrigineProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrigineProduit::class);
    }

//    /**
//     * @return OrigineProduit[] Returns an array of OrigineProduit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OrigineProduit
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
