<?php

namespace App\Repository;

use App\Entity\Livraison;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Livraison>
 *
 * @method Livraison|null find($id, $lockMode = null, $lockVersion = null)
 * @method Livraison|null findOneBy(array $criteria, array $orderBy = null)
 * @method Livraison[]    findAll()
 * @method Livraison[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LivraisonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Livraison::class);
    }

//    /**
//     * @return Livraison[] Returns an array of Livraison objects
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

//    public function findOneBySomeField($value): ?Livraison
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return array
     */
    public function findLivraisonByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.commande', 'c')
            ->leftJoin('c.facturation', 'f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('l.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('l.dateSaisie', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        $nbrePages = ceil($paginator->count() / $limit);
        
         $result['data'] = $data;
         $result['nbrePages'] = $nbrePages;
         $result['pageEncours'] = $pageEnCours;
         $result['limit'] = $limit;
         
        return $result;
    }

    /**
     * @return array
     */
    public function findLivraisonByLieuBySearchPaginated($lieu, $client, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.commande', 'c')
            ->leftJoin('c.facturation', 'f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.client = :client')
            ->andWhere('l.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'client' => $client,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('l.dateSaisie', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        $nbrePages = ceil($paginator->count() / $limit);
        
         $result['data'] = $data;
         $result['nbrePages'] = $nbrePages;
         $result['pageEncours'] = $pageEnCours;
         $result['limit'] = $limit;
         
        return $result;
    }

     /**
     * @return array
     */
    public function findLivraisonByLieuByPersonnelPaginated($lieu, $personnel, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.commande', 'c')
            ->leftJoin('c.facturation', 'f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.saisiePar = :personnel')
            ->andWhere('l.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'personnel' => $personnel,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('l.dateSaisie', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        $nbrePages = ceil($paginator->count() / $limit);
        
         $result['data'] = $data;
         $result['nbrePages'] = $nbrePages;
         $result['pageEncours'] = $pageEnCours;
         $result['limit'] = $limit;
         
        return $result;
    }

    /**
     * @return array
     */
    public function listeDesProduitsLivresParPeriodeParLieuPagine($startDate, $endDate, $lieu, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.commande', 'c')
            ->leftJoin('c.product', 'p')
            ->leftJoin('c.facturation', 'f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('l.dateLivraison BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('p.designation')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        $nbrePages = ceil($paginator->count() / $limit);
        
         $result['data'] = $data;
         $result['nbrePages'] = $nbrePages;
         $result['pageEncours'] = $pageEnCours;
         $result['limit'] = $limit;
         
        return $result;
    }

    /**
     * @return array
     */
    public function listeDesProduitsLivresParPeriodePagine($startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('l')
            ->leftJoin('l.commande', 'c')
            ->leftJoin('c.product', 'p')
            ->leftJoin('c.facturation', 'f')
            ->andWhere('l.dateLivraison BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('p.designation')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();

        $nbrePages = ceil($paginator->count() / $limit);
        
         $result['data'] = $data;
         $result['nbrePages'] = $nbrePages;
         $result['pageEncours'] = $pageEnCours;
         $result['limit'] = $limit;
         
        return $result;
    }


    /**
     * @return array
     */
    public function listeDesProduitsLivresGroupeParPeriodeParLieu($startDate, $endDate, $lieu): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('l')
            ->select('sum(l.quantiteLiv) as nbre', 'l as livraisons')
            ->leftJoin('l.commande', 'c')
            ->leftJoin('c.facturation', 'f')
            ->leftJoin('c.product', 'p')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('l.dateLivraison BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->addGroupBy('c.product')
            ->addOrderBy('p.designation')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function listeDesProduitsLivresGroupeParPeriode($startDate, $endDate): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('l')
            ->select('sum(l.quantiteLiv) as nbre', 'l as livraisons')
            ->leftJoin('l.commande', 'c')
            ->leftJoin('c.facturation', 'f')
            ->leftJoin('c.product', 'p')
            ->andWhere('l.dateLivraison BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->addGroupBy('c.product')
            ->addOrderBy('p.designation')
            ->getQuery()
            ->getResult();
    }
}
