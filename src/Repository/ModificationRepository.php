<?php

namespace App\Repository;

use App\Entity\Modification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Modification>
 *
 * @method Modification|null find($id, $lockMode = null, $lockVersion = null)
 * @method Modification|null findOneBy(array $criteria, array $orderBy = null)
 * @method Modification[]    findAll()
 * @method Modification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Modification::class);
    }

//    /**
//     * @return Modification[] Returns an array of Modification objects
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

//    public function findOneBySomeField($value): ?Modification
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
