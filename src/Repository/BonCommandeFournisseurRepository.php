<?php

namespace App\Repository;

use App\Entity\BonCommandeFournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BonCommandeFournisseur>
 *
 * @method BonCommandeFournisseur|null find($id, $lockMode = null, $lockVersion = null)
 * @method BonCommandeFournisseur|null findOneBy(array $criteria, array $orderBy = null)
 * @method BonCommandeFournisseur[]    findAll()
 * @method BonCommandeFournisseur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BonCommandeFournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BonCommandeFournisseur::class);
    }

//    /**
//     * @return BonCommandeFournisseur[] Returns an array of BonCommandeFournisseur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BonCommandeFournisseur
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
