<?php

namespace App\Repository;

use App\Entity\CommissionVente;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CommissionVente>
 *
 * @method CommissionVente|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommissionVente|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommissionVente[]    findAll()
 * @method CommissionVente[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommissionVenteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommissionVente::class);
    }

//    /**
//     * @return CommissionVente[] Returns an array of CommissionVente objects
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

//    public function findOneBySomeField($value): ?CommissionVente
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
