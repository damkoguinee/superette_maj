<?php

namespace App\Repository;

use App\Entity\RetourProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RetourProduct>
 *
 * @method RetourProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method RetourProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method RetourProduct[]    findAll()
 * @method RetourProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RetourProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RetourProduct::class);
    }

//    /**
//     * @return RetourProduct[] Returns an array of RetourProduct objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RetourProduct
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
