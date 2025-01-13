<?php

namespace App\Repository;

use App\Entity\RetourProductFournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RetourProductFournisseur>
 *
 * @method RetourProductFournisseur|null find($id, $lockMode = null, $lockVersion = null)
 * @method RetourProductFournisseur|null findOneBy(array $criteria, array $orderBy = null)
 * @method RetourProductFournisseur[]    findAll()
 * @method RetourProductFournisseur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RetourProductFournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RetourProductFournisseur::class);
    }

//    /**
//     * @return RetourProductFournisseur[] Returns an array of RetourProductFournisseur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?RetourProductFournisseur
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
