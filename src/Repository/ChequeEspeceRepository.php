<?php

namespace App\Repository;

use App\Entity\ChequeEspece;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ChequeEspece>
 *
 * @method ChequeEspece|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChequeEspece|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChequeEspece[]    findAll()
 * @method ChequeEspece[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChequeEspeceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChequeEspece::class);
    }

//    /**
//     * @return ChequeEspece[] Returns an array of ChequeEspece objects
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

//    public function findOneBySomeField($value): ?ChequeEspece
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
    public function findVersementByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('c')
            ->Where('c.lieuVente = :lieu')
            ->andWhere('c.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('c.dateOperation', 'DESC')
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
    public function findVersementByLieuBySearchPaginated($lieu, $client, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        // $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('c')
            ->Where('c.lieuVente = :lieu')
            ->andWhere('c.collaborateur = :client')
            ->andWhere('c.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'client' => $client,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('c.dateOperation', 'DESC')
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
        $result = $this->createQueryBuilder('v')
            ->select('MAX(v.id)')
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }
}
