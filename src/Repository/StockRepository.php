<?php

namespace App\Repository;

use App\Entity\Stock;
use App\Entity\Inventaire;
use App\Entity\MouvementProduct;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Stock>
 *
 * @method Stock|null find($id, $lockMode = null, $lockVersion = null)
 * @method Stock|null findOneBy(array $criteria, array $orderBy = null)
 * @method Stock[]    findAll()
 * @method Stock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Stock::class);
    }

//    /**
//     * @return Stock[] Returns an array of Stock objects
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

//    public function findOneBySomeField($value): ?Stock
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
    public function listeDesProduitsDisponiblesParLieu($lieu): array
    {
        return $this->createQueryBuilder('s')
            ->leftJoin('s.product', 'p')
            ->leftJoin('s.stockProduit', 'l')
            ->andWhere('l.lieuVente = :lieu')
            ->andWhere('s.quantite > 0')
            ->setParameter('lieu', $lieu)
            ->orderBy('p.designation', 'ASC')
            ->getQuery()->getResult(); 

    }

    /**
     * @return array
     */
    public function findStocksPaginated($magasin, $search, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.product', 'p')
            ->leftJoin('p.categorie', 'c')
            ->Where('s.stockProduit = :stock ');
            if (empty($search)) {
                $query->andWhere('s.quantite != 0');
            }
            $query->andWhere('p.statut = \'actif\'')
            ->andwhere('p.reference LIKE :search OR p.designation LIKE :search OR c.nameCategorie LIKE :search ')
            ->setParameter('stock', $magasin)
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('p.designation', 'ASC')
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

    /**
     * @return array
     */
    public function listeStockGeneralParMagasinParRecherchePagination($magasin, $search, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.product', 'p')
            ->leftJoin('p.categorie', 'c')
            ->Where('s.stockProduit = :stock ');
            $query->andWhere('p.statut = \'actif\'')
            ->andwhere('p.reference LIKE :search OR p.designation LIKE :search OR c.nameCategorie LIKE :search ')
            ->setParameter('stock', $magasin)
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('p.designation', 'ASC')
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

    /**
     * @return array
     */
    public function stockParTypePaginated($magasin, $type, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.product', 'p')
            ->leftJoin('p.categorie', 'c')
            ->Where('s.stockProduit = :stock ')
            ->andWhere('p.typeProduit = :type')
            ->andWhere('s.quantite != 0')
            ->andWhere('p.statut = \'actif\'')
            ->setParameter('stock', $magasin)
            ->setParameter('type', $type)
            ->orderBy('p.designation', 'ASC')
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

    /**
     * @return array
     */
    public function findStocksForApproInitPaginated($magasin, $search, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.product', 'p')
            ->leftJoin('p.categorie', 'c')
            ->Where('s.stockProduit = :stock ')
            ->andWhere('p.statut = \'actif\'')
            ->andwhere('p.reference LIKE :search OR p.designation LIKE :search OR c.nameCategorie LIKE :search ')
            ->setParameter('stock', $magasin)
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('p.categorie', 'ASC')
            ->addOrderBy('p.designation')
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

    /**
     * @return array
     */
    public function findStocksByCodeBarrePaginated($magasin, $search, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.product', 'p')
            ->leftJoin('p.categorie', 'c')
            ->Where('s.stockProduit = :stock ')
            ->andWhere('p.statut = \'actif\'')
            ->andwhere('p.codeBarre = :search ')
            ->setParameter('stock', $magasin)
            ->setParameter('search', $search)
            ->orderBy('p.categorie', 'ASC')
            ->addOrderBy('p.designation')
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

    /**
     * @return array
     */
    public function findStocksForTransfertPaginated($magasin, $search, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('s')
            ->leftJoin('s.product', 'p')
            ->leftJoin('p.categorie', 'c')
            ->Where('s.stockProduit = :stock ')
            ->andWhere('s.quantite > 0')
            ->andWhere('p.statut = \'actif\'')
            ->andwhere('p.reference LIKE :search OR p.designation LIKE :search OR c.nameCategorie LIKE :search ')
            ->setParameter('stock', $magasin)
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('p.categorie', 'ASC')
            ->addOrderBy('p.designation')
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



    /**
     * @return array
     */
    public function findProductsPaginated($inventaire, $magasin, $search, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('s')
            ->select('s as entity', 'i.id as id_inv', 'i.ecart', 'i.quantiteInv as qtite_inv', 'i.statut')
            ->leftJoin(Inventaire::class, 'i', 'WITH', 'i.product = s.product')
            ->leftJoin('s.product', 'p')
            ->leftJoin('p.categorie', 'c')
            ->where('p.reference LIKE :search OR p.designation LIKE :search OR c.nameCategorie LIKE :search ')
            ->andWhere('s.stockProduit = :stock')
            ->andWhere('p.statut = \'actif\'')
            ->andWhere('i.stock = :stock OR i.id IS NULL')
            ->andWhere('i.inventaire = :inventaire OR i.id IS NULL')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('stock', $magasin)
            ->setParameter('inventaire', $inventaire)
            ->addOrderBy('i.id', 'ASC')
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

    /**
     * @return int|null
     */
    public function sumQuantiteProduct($id_product, $stock): ?int
    {
        $query = $this->createQueryBuilder('s')
            ->select('sum(s.quantite) as totalQuantite')
            ->andWhere('s.stockProduit IN (:stock)')
            ->andWhere('s.product = :product')
            ->setParameters([
                'stock' => $stock,
                'product' => $id_product,
            ])
            ->getQuery()
            ->getSingleScalarResult();
        return $query;
    }

    /**
     * @return int|null
     */
    public function sumQuantiteByProductByLieu($id_product, $lieu): ?array
    {
        $query = $this->createQueryBuilder('s')
            ->select('sum(s.quantite) as totalQuantite', 'l.id as id_stock', 'l.nomStock as nom')
            ->leftJoin('s.stockProduit', 'l')
            ->andWhere('l.lieuVente = :lieu')
            ->andWhere('s.product = :product')
            ->setParameters([
                'lieu' => $lieu,
                'product' => $id_product,
            ])
            ->addGroupBy('l.nomStock')
            ->getQuery()
            ->getResult();
        return $query;
    }

    /**
     * @return int|null
     */
    public function disponibiliteProduitParLieu($id_product, $lieu): ?float
    {
        $query = $this->createQueryBuilder('s')
            ->select('sum(s.quantite) as totalQuantite')
            ->leftJoin('s.stockProduit', 'l')
            ->andWhere('l.lieuVente = :lieu')
            ->andWhere('s.product = :product')
            ->setParameters([
                'lieu' => $lieu,
                'product' => $id_product,
            ])
            ->getQuery()
            ->getSingleScalarResult();
        return $query;
    }

    /**
     * @return int|null
     */
    public function sumQuantiteByProduct($id_product): ?array
    {
        $query = $this->createQueryBuilder('s')
            ->select('sum(s.quantite) as totalQuantite', 'l.id as id_stock', 'l.nomStock as nom')
            ->leftJoin('s.stockProduit', 'l')
            ->andWhere('s.product = :product')
            ->setParameters([
                'product' => $id_product,
            ])
            ->addGroupBy('l.nomStock')
            ->getQuery()
            ->getResult();
        return $query;
    }

    /**
     * @return float|null
     */
    public function quantiteDispoProduct($id_product): ?float
    {
        $query = $this->createQueryBuilder('s')
            ->select('sum(s.quantite) as totalQuantite')
            ->andWhere('s.product = :product')
            ->setParameters([
                'product' => $id_product,
            ])
            ->getQuery()
            ->getSingleScalarResult();
        return $query;
    }
    

    public function soldeStockGeneralGroupeParStock(): array
    {    
        return $this->createQueryBuilder('s')
            ->select('sum(s.quantite * s.prixRevient) as montantStock', 'l.nomStock as nomStock')
            ->leftJoin('s.stockProduit', 'l')
            ->addGroupBy('s.stockProduit')
            ->getQuery()
            ->getResult();
    }

    public function soldeStockGeneralGroupeParStockParlieu($lieu): array
    {    
        return $this->createQueryBuilder('s')
            ->select('sum(s.quantite * s.prixRevient) as montantStock', 'l.nomStock as nomStock')
            ->leftJoin('s.stockProduit', 'l')
            ->andWhere('l.lieuVente = :lieu')
            ->setParameter('lieu', $lieu)
            ->addGroupBy('s.stockProduit')
            ->getQuery()
            ->getResult();
    }

    public function soldeStockGeneral(): ?float
    {    
        return $this->createQueryBuilder('s')
            ->select('sum(s.quantite * s.prixRevient) as montantStock')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function soldeStockGeneralParLieu($lieu): ?float
    {    
        return $this->createQueryBuilder('s')
            ->select('sum(s.quantite * s.prixRevient) as montantStock')
            ->leftJoin('s.stockProduit', 'l')
            ->andWhere('l.lieuVente = :lieu')
            ->setParameter('lieu', $lieu)
            ->getQuery()
            ->getSingleScalarResult();
    }


    /**
     * @return int|null
     */
    public function totalQuantiteParProduit($produit): ?array
    {
        $query = $this->createQueryBuilder('s')
            ->select('sum(s.quantite) as totalQuantite', 's as product')
            ->andWhere('s.product = :product')
            ->setParameter('product', $produit)
            ->getQuery()
            ->getResult();
        return $query;
    }

    /**
     * @return int|null
     */
    public function totalQuantiteParProduitParLieu($produit, $lieu): ?array
    {
        $query = $this->createQueryBuilder('s')
            ->select('sum(s.quantite) as totalQuantite', 's as product')
            ->leftJoin('s.stockProduit', 'l')
            ->andWhere('l.lieuVente = :lieu')
            ->andWhere('s.product = :produit')
            ->setParameter('lieu', $lieu)
            ->setParameter('produit', $produit)
            ->getQuery()
            ->getResult();
        return $query;
    }


    /**
     * @return Stock[] Returns an array of Stock objects
    */
    public function StockGeneral(): array
    {    
        return $this->createQueryBuilder('s')
            ->leftJoin('s.product', 'p')
            ->addOrderBy('p.designation', 'ASC')
            ->getQuery()
            ->getResult();
    }

    
    
}
