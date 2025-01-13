<?php

namespace App\Repository;

use App\Entity\FraisSup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FraisSup>
 *
 * @method FraisSup|null find($id, $lockMode = null, $lockVersion = null)
 * @method FraisSup|null findOneBy(array $criteria, array $orderBy = null)
 * @method FraisSup[]    findAll()
 * @method FraisSup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FraisSupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FraisSup::class);
    }

//    /**
//     * @return FraisSup[] Returns an array of FraisSup objects
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

//    public function findOneBySomeField($value): ?FraisSup
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
