<?php

namespace App\Repository;

use App\Entity\DeleteDecaissement;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<DeleteDecaissement>
 *
 * @method DeleteDecaissement|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeleteDecaissement|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeleteDecaissement[]    findAll()
 * @method DeleteDecaissement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeleteDecaissementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeleteDecaissement::class);
    }

//    /**
//     * @return DeleteDecaissement[] Returns an array of DeleteDecaissement objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DeleteDecaissement
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

   
}
