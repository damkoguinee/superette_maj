<?php

namespace App\Repository;

use App\Entity\GestionCheque;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GestionCheque>
 *
 * @method GestionCheque|null find($id, $lockMode = null, $lockVersion = null)
 * @method GestionCheque|null findOneBy(array $criteria, array $orderBy = null)
 * @method GestionCheque[]    findAll()
 * @method GestionCheque[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GestionChequeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GestionCheque::class);
    }

//    /**
//     * @return GestionCheque[] Returns an array of GestionCheque objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?GestionCheque
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
