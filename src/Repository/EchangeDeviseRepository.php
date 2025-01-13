<?php

namespace App\Repository;

use App\Entity\EchangeDevise;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<EchangeDevise>
 *
 * @method EchangeDevise|null find($id, $lockMode = null, $lockVersion = null)
 * @method EchangeDevise|null findOneBy(array $criteria, array $orderBy = null)
 * @method EchangeDevise[]    findAll()
 * @method EchangeDevise[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EchangeDeviseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EchangeDevise::class);
    }

//    /**
//     * @return EchangeDevise[] Returns an array of EchangeDevise objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?EchangeDevise
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
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
        $query = $this->createQueryBuilder('e')
            ->Where('e.lieuVente = :lieu')
            ->andWhere('e.dateEchange BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('e.dateEchange', 'DESC')
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
        $query = $this->createQueryBuilder('e')
            ->Where('e.lieuVente = :lieu')
            ->andWhere('e.caisseOrigine = :caisse')
            ->andWhere('e.dateEchange BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'caisse' => $caisse,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('e.dateEchange', 'DESC')
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
        $result = $this->createQueryBuilder('e')
            ->select('MAX(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }
}
