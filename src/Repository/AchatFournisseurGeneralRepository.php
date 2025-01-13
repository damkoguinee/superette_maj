<?php

namespace App\Repository;

use App\Entity\AchatFournisseurGeneral;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AchatFournisseurGeneral>
 *
 * @method AchatFournisseurGeneral|null find($id, $lockMode = null, $lockVersion = null)
 * @method AchatFournisseurGeneral|null findOneBy(array $criteria, array $orderBy = null)
 * @method AchatFournisseurGeneral[]    findAll()
 * @method AchatFournisseurGeneral[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchatFournisseurGeneralRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AchatFournisseurGeneral::class);
    }

//    /**
//     * @return AchatFournisseurGeneral[] Returns an array of AchatFournisseurGeneral objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AchatFournisseurGeneral
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
