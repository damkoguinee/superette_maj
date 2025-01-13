<?php

namespace App\Repository;

use App\Entity\LiaisonProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LiaisonProduit>
 *
 * @method LiaisonProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method LiaisonProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method LiaisonProduit[]    findAll()
 * @method LiaisonProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LiaisonProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LiaisonProduit::class);
    }

//    /**
//     * @return LiaisonProduit[] Returns an array of LiaisonProduit objects
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

//    public function findOneBySomeField($value): ?LiaisonProduit
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
    * @return Products[] Returns an array of Products objects
    */
    public function findLiaisonByProduct($value): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.produit1 = :val OR p.produit2 = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
