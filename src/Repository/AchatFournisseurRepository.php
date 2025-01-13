<?php

namespace App\Repository;

use App\Entity\AchatFournisseur;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<AchatFournisseur>
 *
 * @method AchatFournisseur|null find($id, $lockMode = null, $lockVersion = null)
 * @method AchatFournisseur|null findOneBy(array $criteria, array $orderBy = null)
 * @method AchatFournisseur[]    findAll()
 * @method AchatFournisseur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AchatFournisseurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AchatFournisseur::class);
    }

//    /**
//     * @return AchatFournisseur[] Returns an array of AchatFournisseur objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?AchatFournisseur
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

/**
     * @return array
     */
    public function findAchatByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('a')
            ->Where('a.lieuVente = :lieu')
            ->andWhere('a.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('a.dateSaisie', 'DESC')
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
    public function findAchatByLieuBySearchPaginated($lieu, $client, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('a')
            ->Where('a.lieuVente = :lieu')
            ->andWhere('a.fournisseur = :client')
            ->andWhere('a.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'client' => $client,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('a.dateSaisie', 'DESC')
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
