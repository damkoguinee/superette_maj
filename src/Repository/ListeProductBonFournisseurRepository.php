<?php

namespace App\Repository;

use App\Entity\ListeProductBonFournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListeProductBonFournisseur>
 *
 * @method ListeProductBonFournisseur|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListeProductBonFournisseur|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListeProductBonFournisseur[]    findAll()
 * @method ListeProductBonFournisseur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListeProductBonFournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeProductBonFournisseur::class);
    }

//    /**
//     * @return ListeProductBonFournisseur[] Returns an array of ListeProductBonFournisseur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ListeProductBonFournisseur
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
