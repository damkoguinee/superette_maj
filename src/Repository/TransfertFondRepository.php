<?php

namespace App\Repository;

use App\Entity\TransfertFond;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<TransfertFond>
 *
 * @method TransfertFond|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransfertFond|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransfertFond[]    findAll()
 * @method TransfertFond[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransfertFondRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransfertFond::class);
    }

//    /**
//     * @return TransfertFond[] Returns an array of TransfertFond objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?TransfertFond
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return array
     */
    public function findTransfertByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('t')
            ->Where('t.lieuVente = :lieu')
            ->andWhere('t.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('t.dateOperation', 'DESC')
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
    public function findTransfertByLieuBySearchPaginated($lieu, $caisse, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        // $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('t')
            ->Where('t.lieuVente = :lieu')
            ->andWhere('t.caisseDepart = :caisse')
            ->andWhere('t.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'caisse' => $caisse,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('t.dateOperation', 'DESC')
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
    public function findReceptionTransfertByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        // $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('t')
            ->Where('t.lieuVenteReception = :lieu')
            ->andWhere('t.type != :type')
            ->andWhere('t.etat = :etat')
            ->andWhere('t.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'type' => 'autres',
                'etat' => 'envoyer',
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('t.dateOperation', 'DESC')
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

    public function findMaxId(): ?int
    {
        $result = $this->createQueryBuilder('t')
            ->select('MAX(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }
}
