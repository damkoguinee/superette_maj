<?php

namespace App\Repository;

use App\Entity\Epaisseurs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Epaisseurs>
 *
 * @method Epaisseurs|null find($id, $lockMode = null, $lockVersion = null)
 * @method Epaisseurs|null findOneBy(array $criteria, array $orderBy = null)
 * @method Epaisseurs[]    findAll()
 * @method Epaisseurs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpaisseursRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Epaisseurs::class);
    }

//    /**
//     * @return Epaisseurs[] Returns an array of Epaisseurs objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Epaisseurs
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
