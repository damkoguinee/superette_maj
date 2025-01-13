<?php

namespace App\Repository;

use App\Entity\SortieStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SortieStock>
 *
 * @method SortieStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method SortieStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method SortieStock[]    findAll()
 * @method SortieStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SortieStock::class);
    }

//    /**
//     * @return SortieStock[] Returns an array of SortieStock objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SortieStock
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
