<?php

namespace App\Repository;

use App\Entity\FactureFrais;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<FactureFrais>
 *
 * @method FactureFrais|null find($id, $lockMode = null, $lockVersion = null)
 * @method FactureFrais|null findOneBy(array $criteria, array $orderBy = null)
 * @method FactureFrais[]    findAll()
 * @method FactureFrais[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactureFraisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FactureFrais::class);
    }

//    /**
//     * @return FactureFrais[] Returns an array of FactureFrais objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FactureFrais
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

/**
     * @return array
     */
    public function findFactureFraisByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('f.dateSaisie', 'DESC')
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
    public function findFactureFraisByLieuBySearchPaginated($lieu, $client, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.fournisseur = :client')
            ->andWhere('f.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'client' => $client,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('f.dateSaisie', 'DESC')
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
