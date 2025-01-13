<?php

namespace App\Repository;

use App\Entity\Decaissement;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Decaissement>
 *
 * @method Decaissement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Decaissement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Decaissement[]    findAll()
 * @method Decaissement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DecaissementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Decaissement::class);
    }

//    /**
//     * @return Decaissement[] Returns an array of Decaissement objects
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

//    public function findOneBySomeField($value): ?Decaissement
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
    public function findDecaissementByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('d')
            ->Where('d.lieuVente = :lieu')
            ->andWhere('d.dateDecaissement BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('d.id', 'DESC')
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
    public function findDecaissementByLieuBySearchPaginated($lieu, $client, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('d')
            ->Where('d.lieuVente = :lieu')
            ->andWhere('d.client = :client')
            ->andWhere('d.dateDecaissement BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'client' => $client,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('d.dateDecaissement', 'DESC')
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
    public function listeDesDecaissementsParLieuParPeriode($lieu, $startDate, $endDate): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('d')
            ->Where('d.lieuVente = :lieu')
            ->andWhere('d.dateDecaissement BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('d.id', 'DESC');
         
        return $query->getQuery()->getResult();
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
