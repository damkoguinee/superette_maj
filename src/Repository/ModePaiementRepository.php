<?php

namespace App\Repository;

use App\Entity\ModePaiement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ModePaiement>
 *
 * @method ModePaiement|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModePaiement|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModePaiement[]    findAll()
 * @method ModePaiement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModePaiementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModePaiement::class);
    }

//    /**
//     * @return ModePaiement[] Returns an array of ModePaiement objects
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

//    public function findOneBySomeField($value): ?ModePaiement
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
