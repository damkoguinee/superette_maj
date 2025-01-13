<?php

namespace App\Repository;

use App\Entity\ClotureCaisse;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ClotureCaisse>
 *
 * @method ClotureCaisse|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClotureCaisse|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClotureCaisse[]    findAll()
 * @method ClotureCaisse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClotureCaisseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ClotureCaisse::class);
    }

    //    /**
    //     * @return ClotureCaisse[] Returns an array of ClotureCaisse objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(100)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ClotureCaisse
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * @return array
     */
    public function clotureSurplusParPeriodeParLieu($lieu, $startDate, $endDate): float
    {
        return $this->createQueryBuilder('c')
        ->select('SUM(c.difference) as difference')
            ->Where('c.lieuVente = :lieu')
            ->andWhere('c.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();

    }

    /**
     * @return array
     */
    public function listeDesCloturesParPeriodeParLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('c')
            ->Where('c.lieuVente = :lieu')
            ->andWhere('c.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('c.dateSaisie', 'DESC')
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
