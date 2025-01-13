<?php

namespace App\Repository;

use App\Entity\AnomalieProduit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AnomalieProduit>
 *
 * @method AnomalieProduit|null find($id, $lockMode = null, $lockVersion = null)
 * @method AnomalieProduit|null findOneBy(array $criteria, array $orderBy = null)
 * @method AnomalieProduit[]    findAll()
 * @method AnomalieProduit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnomalieProduitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AnomalieProduit::class);
    }

//    /**
//     * @return AnomalieProduit[] Returns an array of AnomalieProduit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AnomalieProduit
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return array
     */

     public function totalAnomalieParProduitParPeriode($produit, $startDate, $endDate): array
     {
         $endDate = (new \DateTime($endDate))->modify('+1 day');
         return $this->createQueryBuilder('a')
             ->select('SUM((a.quantite)) as nbre, SUM(a.quantite * a.prixRevient) as totalAnomalie')
             ->andWhere('a.dateAnomalie BETWEEN :startDate AND :endDate')
             ->andWhere('a.product = :produit')
             ->setParameter('startDate', $startDate)
             ->setParameter('endDate', $endDate)
             ->setParameter('produit', $produit)
             ->getQuery()
             ->getResult();
             ;
     }

    /**
     * @return array
     */

     public function totalAnomalieParProduitParPeriodeParLieu($produit, $startDate, $endDate, $lieu): array
     {
         $endDate = (new \DateTime($endDate))->modify('+1 day');
         return $this->createQueryBuilder('a')
             ->select('SUM((a.quantite)) as nbre, SUM(a.quantite * a.prixRevient) as totalAnomalie')
             ->leftJoin('a.stock', 's')
             ->andWhere('a.dateAnomalie BETWEEN :startDate AND :endDate')
             ->andWhere('a.product = :produit')
             ->andWhere('s.lieuVente = :lieu')
             ->setParameter('startDate', $startDate)
             ->setParameter('endDate', $endDate)
             ->setParameter('produit', $produit)
             ->setParameter('lieu', $lieu)
             ->getQuery()
             ->getResult();
             ;
     }

    public function totalAnomalieGeneral(): ?float
    {    
        return $this->createQueryBuilder('a')
            ->select('sum(a.quantite * a.prixRevient) as montantStock')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function totalAnomalieGeneralParLieu($lieu): ?float
    {    
        return $this->createQueryBuilder('a')
            ->select('sum(a.quantite * a.prixRevient) as montantStock')
            ->leftJoin('a.stock', 'l')
            ->andWhere('l.lieuVente = :lieu')
            ->setParameter('lieu', $lieu)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
