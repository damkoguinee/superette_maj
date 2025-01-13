<?php

namespace App\Repository;

use App\Entity\CompteOperation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompteOperation>
 *
 * @method CompteOperation|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompteOperation|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompteOperation[]    findAll()
 * @method CompteOperation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CompteOperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompteOperation::class);
    }

//    /**
//     * @return CompteOperation[] Returns an array of CompteOperation objects
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

//    public function findOneBySomeField($value): ?CompteOperation
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
