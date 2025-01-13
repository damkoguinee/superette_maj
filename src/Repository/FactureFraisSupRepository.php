<?php

namespace App\Repository;

use App\Entity\FactureFraisSup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FactureFraisSup>
 *
 * @method FactureFraisSup|null find($id, $lockMode = null, $lockVersion = null)
 * @method FactureFraisSup|null findOneBy(array $criteria, array $orderBy = null)
 * @method FactureFraisSup[]    findAll()
 * @method FactureFraisSup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactureFraisSupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactureFraisSup::class);
    }

//    /**
//     * @return FactureFraisSup[] Returns an array of FactureFraisSup objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FactureFraisSup
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
