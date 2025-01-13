<?php

namespace App\Repository;

use App\Entity\LieuxVentes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LieuxVentes>
 *
 * @method LieuxVentes|null find($id, $lockMode = null, $lockVersion = null)
 * @method LieuxVentes|null findOneBy(array $criteria, array $orderBy = null)
 * @method LieuxVentes[]    findAll()
 * @method LieuxVentes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LieuxVentesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LieuxVentes::class);
    }

   /**
    * @return LieuxVentes[] Returns an array of LieuxVentes objects
    */
   public function findAllLieuxVenteExecept($value): array
   {
       return $this->createQueryBuilder('l')
           ->andWhere('l.id != :val')
           ->setParameter('val', $value)
           ->orderBy('l.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }

//    public function findOneBySomeField($value): ?LieuxVentes
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    
}
