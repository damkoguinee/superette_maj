<?php

namespace App\Repository;

use App\Entity\SuppressionFacture;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<SuppressionFacture>
 *
 * @method SuppressionFacture|null find($id, $lockMode = null, $lockVersion = null)
 * @method SuppressionFacture|null findOneBy(array $criteria, array $orderBy = null)
 * @method SuppressionFacture[]    findAll()
 * @method SuppressionFacture[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SuppressionFactureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SuppressionFacture::class);
    }

//    /**
//     * @return SuppressionFacture[] Returns an array of SuppressionFacture objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SuppressionFacture
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }


/**
     * @return array
     */
    public function listeSuppressionFacturePaginated($startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('s')
            ->andWhere('s.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('s.dateSaisie', 'DESC')
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
    public function listeSuppressionFactureParLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('s')
            ->Where('s.lieuVente = :lieu')
            ->andWhere('s.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('s.dateSaisie', 'DESC')
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
}
