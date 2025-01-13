<?php

namespace App\Repository;

use App\Entity\ListeProductAchatFournisseur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListeProductAchatFournisseur>
 *
 * @method ListeProductAchatFournisseur|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListeProductAchatFournisseur|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListeProductAchatFournisseur[]    findAll()
 * @method ListeProductAchatFournisseur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListeProductAchatFournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeProductAchatFournisseur::class);
    }

//    /**
//     * @return ListeProductAchatFournisseur[] Returns an array of ListeProductAchatFournisseur objects
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

//    public function findOneBySomeField($value): ?ListeProductAchatFournisseur
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

        public function totalAchatGeneralParProduitParPeriode($produit, $startDate, $endDate): array
        {
            $endDate = (new \DateTime($endDate))->modify('+1 day');
            return $this->createQueryBuilder('l')
                ->select('SUM((l.quantite)) as nbreAchat, SUM(l.quantite * l.prixAchat) as totalAchat')
                ->leftJoin('l.achatFournisseur', 'a')
                ->andWhere('a.dateFacture BETWEEN :startDate AND :endDate')
                ->andWhere('l.product = :produit')
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->setParameter('produit', $produit)
                ->getQuery()
                ->getResult();
                ;
        }

        public function totalAchatGeneralParProduitParPeriodeParLieu($produit, $startDate, $endDate, $lieu): array
        {
            $endDate = (new \DateTime($endDate))->modify('+1 day');
            return $this->createQueryBuilder('l')
                ->select('SUM((l.quantite)) as nbreAchat, SUM(l.quantite * l.prixAchat) as totalAchat')
                ->leftJoin('l.achatFournisseur', 'a')
                ->andWhere('a.lieuVente = :lieu')
                ->andWhere('a.dateFacture BETWEEN :startDate AND :endDate')
                ->andWhere('l.product = :produit')
                ->setParameter('lieu', $lieu)
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate)
                ->setParameter('produit', $produit)
                ->getQuery()
                ->getResult();
                ;
        }
    }

    
