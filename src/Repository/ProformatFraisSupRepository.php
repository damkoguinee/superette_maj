<?php

namespace App\Repository;

use App\Entity\ProformatFraisSup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProformatFraisSup>
 *
 * @method ProformatFraisSup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProformatFraisSup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProformatFraisSup[]    findAll()
 * @method ProformatFraisSup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProformatFraisSupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProformatFraisSup::class);
    }

//    /**
//     * @return ProformatFraisSup[] Returns an array of ProformatFraisSup objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ProformatFraisSup
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
