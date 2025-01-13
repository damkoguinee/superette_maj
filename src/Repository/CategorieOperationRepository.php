<?php

namespace App\Repository;

use App\Entity\CategorieOperation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategorieOperation>
 *
 * @method CategorieOperation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorieOperation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorieOperation[]    findAll()
 * @method CategorieOperation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieOperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategorieOperation::class);
    }

//    /**
//     * @return CategorieOperation[] Returns an array of CategorieOperation objects
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

//    public function findOneBySomeField($value): ?CategorieOperation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
