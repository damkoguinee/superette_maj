<?php

namespace App\Repository;

use App\Entity\ModifDecaissement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ModifDecaissement>
 *
 * @method ModifDecaissement|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModifDecaissement|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModifDecaissement[]    findAll()
 * @method ModifDecaissement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModifDecaissementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModifDecaissement::class);
    }

//    /**
//     * @return ModifDecaissement[] Returns an array of ModifDecaissement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ModifDecaissement
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
