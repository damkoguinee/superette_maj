<?php

namespace App\Repository;

use App\Entity\ListeTransfertProduct;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<ListeTransfertProduct>
 *
 * @method ListeTransfertProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListeTransfertProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListeTransfertProduct[]    findAll()
 * @method ListeTransfertProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListeTransfertProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeTransfertProduct::class);
    }

//    /**
//     * @return ListeTransfertProduct[] Returns an array of ListeTransfertProduct objects
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

//    public function findOneBySomeField($value): ?ListeTransfertProduct
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
    public function findListeProductsForTransfertPaginated($transfert, $search, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('t')
            ->leftJoin('t.product', 'p')
            ->leftJoin('p.categorie', 'c')
            ->Where('t.transfert = :transfert')
            ->andWhere('p.statut = \'actif\'')
            ->andwhere('p.reference LIKE :search OR p.designation LIKE :search OR c.nameCategorie LIKE :search ')
            ->setParameter('transfert', $transfert)
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('t.id', 'DESC')
            ->addOrderBy('p.categorie', 'ASC')
            ->addOrderBy('p.designation', 'ASC')
            ->setMaxResults($limit)
            ->setFirstResult(($pageEnCours * $limit) - $limit);
        $paginator = new Paginator($query);
        $data = $paginator->getQuery()->getResult();
        // on calcule le nombre des pages

        $nbrePages = ceil($paginator->count() / $limit);
         // on remplit le tableau
        
         $result['data'] = $data;
         $result['nbrePages'] = $nbrePages;
         $result['pageEncours'] = $pageEnCours;
         $result['limit'] = $limit;
        return $result;
    }
}
