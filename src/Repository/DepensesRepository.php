<?php

namespace App\Repository;

use App\Entity\Depenses;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Depenses>
 *
 * @method Depenses|null find($id, $lockMode = null, $lockVersion = null)
 * @method Depenses|null findOneBy(array $criteria, array $orderBy = null)
 * @method Depenses[]    findAll()
 * @method Depenses[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DepensesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Depenses::class);
    }

//    /**
//     * @return Depenses[] Returns an array of Depenses objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Depenses
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return array
     */
    public function findDepensesByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('d')
            ->Where('d.lieuVente = :lieu')
            ->andWhere('d.dateDepense BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('d.dateDepense', 'DESC')
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
    public function findDepensesByLieuBySearchPaginated($lieu, $categorie, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        // $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('d')
            ->Where('d.lieuVente = :lieu')
            ->andWhere('d.categorieDepense = :cat')
            ->andWhere('d.dateDepense BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'cat' => $categorie,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('d.dateDepense', 'DESC')
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
    public function totalDepensesParPeriodeParLieu($lieu, $startDate, $endDate): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('d')
            ->select('SUM(d.montant) as montantTotal', 'dev.nomDevise', 'dev.id as id_devise')
            ->leftJoin('d.devise', 'dev')
            ->Where('d.lieuVente = :lieu')
            ->andWhere('d.dateDepense BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('d.devise')
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return array
     */
    public function totalDepensesParPeriodeParLieuParCategorie($lieu, $categorie, $startDate, $endDate): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('d')
            ->select('SUM(d.montant) as montantTotal', 'dev.nomDevise', 'dev.id as id_devise')
            ->leftJoin('d.devise', 'dev')
            ->Where('d.lieuVente = :lieu')
            ->andWhere('d.categorieDepense = :cat')
            ->andWhere('d.dateDepense BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('cat', $categorie)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('d.devise')
            ->getQuery()
            ->getResult()
            ;
    }

    public function totalDepensesParPeriodeParLieuParDevise($startDate, $endDate, $lieu, $devise): ?float
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('d')
            ->select('SUM(d.montant) as montantTotal')
            ->leftJoin('d.devise', 'dev')
            ->Where('d.lieuVente = :lieu')
            ->andWhere('d.devise = :devise')
            ->andWhere('d.dateDepense BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('devise', $devise)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
            ;
    }

    public function totalDepensesParPeriode($startDate, $endDate): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('d')
            ->select('SUM(d.montant) as montantTotal', 'dev.id as id_devise')
            ->leftJoin('d.devise', 'dev')
            ->andWhere('d.dateDepense BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->addGroupBy('d.devise')
            ->getQuery()
            ->getResult();
            ;
    }

    public function findMaxId(): ?int
    {
        $result = $this->createQueryBuilder('d')
            ->select('MAX(d.id)')
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }
}
