<?php

namespace App\Repository;

use App\Entity\ListeInventaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListeInventaire>
 *
 * @method ListeInventaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListeInventaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListeInventaire[]    findAll()
 * @method ListeInventaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListeInventaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeInventaire::class);
    }

//    /**
//     * @return ListeInventaire[] Returns an array of ListeInventaire objects
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

//    public function findOneBySomeField($value): ?ListeInventaire
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
