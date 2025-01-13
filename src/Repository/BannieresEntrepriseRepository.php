<?php

namespace App\Repository;

use App\Entity\BannieresEntreprise;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BannieresEntreprise>
 *
 * @method BannieresEntreprise|null find($id, $lockMode = null, $lockVersion = null)
 * @method BannieresEntreprise|null findOneBy(array $criteria, array $orderBy = null)
 * @method BannieresEntreprise[]    findAll()
 * @method BannieresEntreprise[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BannieresEntrepriseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BannieresEntreprise::class);
    }

//    /**
//     * @return BannieresEntreprise[] Returns an array of BannieresEntreprise objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BannieresEntreprise
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
