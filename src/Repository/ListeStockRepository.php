<?php

namespace App\Repository;

use App\Entity\ListeStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListeStock>
 *
 * @method ListeStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListeStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListeStock[]    findAll()
 * @method ListeStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListeStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeStock::class);
    }

//    /**
//     * @return ListeStock[] Returns an array of ListeStock objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ListeStock
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
