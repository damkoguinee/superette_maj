<?php

namespace App\Repository;

use App\Entity\TransfertProducts;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransfertProducts>
 *
 * @method TransfertProducts|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransfertProducts|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransfertProducts[]    findAll()
 * @method TransfertProducts[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransfertProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransfertProducts::class);
    }

//    /**
//     * @return TransfertProducts[] Returns an array of TransfertProducts objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TransfertProducts
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
