<?php

namespace App\Repository;

use App\Entity\CategorieDecaissement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CategorieDecaissement>
 *
 * @method CategorieDecaissement|null find($id, $lockMode = null, $lockVersion = null)
 * @method CategorieDecaissement|null findOneBy(array $criteria, array $orderBy = null)
 * @method CategorieDecaissement[]    findAll()
 * @method CategorieDecaissement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieDecaissementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CategorieDecaissement::class);
    }

//    /**
//     * @return CategorieDecaissement[] Returns an array of CategorieDecaissement objects
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

//    public function findOneBySomeField($value): ?CategorieDecaissement
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
