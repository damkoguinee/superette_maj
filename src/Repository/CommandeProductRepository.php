<?php

namespace App\Repository;

use App\Entity\Livraison;
use App\Entity\ListeStock;
use App\Entity\CommandeProduct;
use App\Entity\FactureFraisSup;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<CommandeProduct>
 *
 * @method CommandeProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method CommandeProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method CommandeProduct[]    findAll()
 * @method CommandeProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CommandeProduct::class);
    }

//    /**
//     * @return CommandeProduct[] Returns an array of CommandeProduct objects
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

//    public function findOneBySomeField($value): ?CommandeProduct
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
    public function findAllCommand($facturation): array
    {
        $result = [];
        $query = $this->createQueryBuilder('c')
            ->select('c as commande', 's.nomStock as livraison')
            ->leftJoin(Livraison::class, 'l', 'WITH', 'l.commande = c.id')
            ->leftJoin(ListeStock::class, 's', 'WITH', 's.id = l.stock')
            ->where('c.facturation = :facturation ')
            ->setParameter('facturation', $facturation)
            ->addOrderBy('c.id', 'ASC')
            ->addOrderBy('c.product', 'ASC');
        $data= $query->getQuery()
                    ->getResult();
        $result['data'] = $data; 
        
       // Query for FactureFraisSup
        $factureFraisSupRepository = $this->_em->getRepository(FactureFraisSup::class);
        $queryFrais = $factureFraisSupRepository->createQueryBuilder('f')
            ->where('f.facturation = :facturation')
            ->setParameter('facturation', $facturation)
            ->getQuery()
            ->getResult();
        $result['frais'] = $queryFrais;
        return $result;
    }

    /**
     * @return array
     */
    public function listeDesProduitsVendusParLieu($lieu): array
    {
        return $this->createQueryBuilder('c')
            // ->select('c as produit')
            ->leftJoin('c.facturation', 'f')
            ->Where('f.lieuVente = :lieu')
            ->setParameter('lieu', $lieu)
            ->addOrderBy('c.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function listeDesProduitsVendus(): array
    {
        return $this->createQueryBuilder('c')
            // ->select('c as produit')
            ->leftJoin('c.facturation', 'f')
            ->addOrderBy('c.id')
            ->getQuery()
            ->getResult();
    }


    /**
     * @return array
     */
    public function listeDesProduitsVendusParPeriodeParLieuPagine($startDate, $endDate, $lieu, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.facturation', 'f')
            ->leftJoin('c.product', 'p')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('p.designation')
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
    public function listeDesProduitsVendusParPeriodePagine($startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.facturation', 'f')
            ->leftJoin('c.product', 'p')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('p.designation')
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
    public function listeDesProduitsVendusGroupeParPeriodeParLieu($startDate, $endDate, $lieu): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('c')
            ->select('sum(c.quantite) as nbre', 'sum(c.quantiteLivre) as nbreLivre', 'c as commandes')
            ->leftJoin('c.facturation', 'f')
            ->leftJoin('c.product', 'p')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->addGroupBy('c.product')
            ->addOrderBy('p.designation')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function listeDesProduitsVendusGroupeParPeriode($startDate, $endDate): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('c')
            ->select('sum(c.quantite) as nbre', 'sum(c.quantiteLivre) as nbreLivre', 'c as commandes')
            ->leftJoin('c.facturation', 'f')
            ->leftJoin('c.product', 'p')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->addGroupBy('c.product')
            ->addOrderBy('p.designation')
            ->getQuery()
            ->getResult();
    }

    public function beneficeDesVentesParPeriodeParLieu($startDate, $endDate, $lieu): ?float
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('c')
            ->select('SUM((c.quantite * c.prixVente) - (c.quantite * c.prixRevient)) as montantBenefice')
            ->leftJoin('c.facturation', 'f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
            ;
    }

    /**
     * @return array
     */

    public function topVenteProduitGeneralParPeriode($startDate, $endDate): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('c')
            ->select('SUM((c.quantite)) as totalVente, SUM(c.quantite * c.prixVente) as montantTotal, c as commande')
            ->leftJoin('c.facturation', 'f')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->addGroupBy('c.product')
            ->addOrderBy('totalVente', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
            ;
    }

    /**
     * @return array
     */

     public function topVenteProduitGeneralParPeriodeGroupeParClient($startDate, $endDate): array
     {
         $endDate = (new \DateTime($endDate))->modify('+1 day');
         return $this->createQueryBuilder('c')
             ->select('SUM((c.quantite)) as totalVente, SUM(c.quantite * c.prixVente) as montantTotal, c as commande')
             ->leftJoin('c.facturation', 'f')
             ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
             ->setParameter('startDate', $startDate)
             ->setParameter('endDate', $endDate)
             ->addGroupBy('f.client')
             ->addOrderBy('totalVente', 'DESC')
             ->setMaxResults(50)
             ->getQuery()
             ->getResult();
             ;
     }

    /**
     * @return array
     */

     public function topVenteProduitGeneralParPeriodeParLieuGroupeParClient($startDate, $endDate, $lieu): array
     {
         $endDate = (new \DateTime($endDate))->modify('+1 day');
         return $this->createQueryBuilder('c')
             ->select('SUM((c.quantite)) as totalVente, SUM(c.quantite * c.prixVente) as montantTotal, c as commande')
             ->leftJoin('c.facturation', 'f')
             ->andWhere('f.lieuVente = :lieu')
             ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
             ->setParameter('lieu', $lieu)
             ->setParameter('startDate', $startDate)
             ->setParameter('endDate', $endDate)
             ->addGroupBy('f.client')
             ->addOrderBy('totalVente', 'DESC')
             ->setMaxResults(50)
             ->getQuery()
             ->getResult();
             ;
     }

    /**
     * @return array
     */

     public function topVenteBeneficeProduitGeneralParPeriodeParLieuGroupeParClient($startDate, $endDate, $lieu): array
     {
         $endDate = (new \DateTime($endDate))->modify('+1 day');
         return $this->createQueryBuilder('c')
             ->select('SUM((c.quantite)) as totalVente, SUM((c.quantite * c.prixVente - c.quantite * c.prixRevient)) as benefice, c as commande')
             ->leftJoin('c.facturation', 'f')
             ->andWhere('f.lieuVente = :lieu')
             ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
             ->setParameter('lieu', $lieu)
             ->setParameter('startDate', $startDate)
             ->setParameter('endDate', $endDate)
             ->addGroupBy('f.client')
             ->addOrderBy('benefice', 'DESC')
             ->setMaxResults(50)
             ->getQuery()
             ->getResult();
             ;
     }

     

    /**
     * @return array
     */

    public function topVenteBeneficeProduitGeneralParPeriodeParLieu($startDate, $endDate, $lieu): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('c')
            ->select('SUM((c.quantite)) as totalVente, SUM((c.quantite * c.prixVente - c.quantite * c.prixRevient)) as benefice, c as commande')
            ->leftJoin('c.facturation', 'f')
            ->andWhere('f.lieuVente = :lieu')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->addGroupBy('c.product')
            ->addOrderBy('benefice', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
            ;
    }

    /**
     * @return array
     */

     public function topVenteBeneficeProduitGeneralParPeriodeParClient($startDate, $endDate, $client): array
     {
         $endDate = (new \DateTime($endDate))->modify('+1 day');
         return $this->createQueryBuilder('c')
             ->select('SUM((c.quantite)) as totalVente, SUM((c.quantite * c.prixVente - c.quantite * c.prixRevient)) as benefice, c as commande')
             ->leftJoin('c.facturation', 'f')
             ->andWhere('f.client = :client')
             ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
             ->setParameter('client', $client)
             ->setParameter('startDate', $startDate)
             ->setParameter('endDate', $endDate)
             ->addGroupBy('c.product')
             ->addOrderBy('benefice', 'DESC')
             ->setMaxResults(50)
             ->getQuery()
             ->getResult();
             ;
     }

    /**
     * @return array
     */

    public function topVenteProduitGeneralParPeriodeParLieu($startDate, $endDate, $lieu): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('c')
            ->select('SUM((c.quantite)) as totalVente, SUM(c.quantite * c.prixVente) as montantTotal, c as commande')
            ->leftJoin('c.facturation', 'f')
            ->andWhere('f.lieuVente = :lieu')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->addGroupBy('c.product')
            ->addOrderBy('totalVente', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
            ;
    }

    /**
     * @return array
     */

     public function topVenteProduitGeneralParPeriodeParClient($startDate, $endDate, $client): array
     {
         $endDate = (new \DateTime($endDate))->modify('+1 day');
         return $this->createQueryBuilder('c')
             ->select('SUM((c.quantite)) as totalVente, SUM(c.quantite * c.prixVente) as montantTotal, c as commande')
             ->leftJoin('c.facturation', 'f')
             ->andWhere('f.client = :client')
             ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
             ->setParameter('client', $client)
             ->setParameter('startDate', $startDate)
             ->setParameter('endDate', $endDate)
             ->addGroupBy('c.product')
             ->addOrderBy('totalVente', 'DESC')
             ->setMaxResults(50)
             ->getQuery()
             ->getResult();
             ;
     }

    /**
     * @return array
     */

    public function topVenteBeneficeProduitGeneralParPeriode($startDate, $endDate): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('c')
            ->select('SUM((c.quantite)) as totalVente, SUM((c.quantite * c.prixVente - c.quantite * c.prixRevient)) as benefice, c as commande')
            ->leftJoin('c.facturation', 'f')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->addGroupBy('c.product')
            ->addOrderBy('benefice', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
            ;
    }

    /**
     * @return array
     */

     public function topVenteBeneficeProduitGeneralParPeriodeGroupeParClient($startDate, $endDate): array
     {
         $endDate = (new \DateTime($endDate))->modify('+1 day');
         return $this->createQueryBuilder('c')
             ->select('SUM((c.quantite)) as totalVente, SUM((c.quantite * c.prixVente - c.quantite * c.prixRevient)) as benefice, c as commande')
             ->leftJoin('c.facturation', 'f')
             ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
             ->setParameter('startDate', $startDate)
             ->setParameter('endDate', $endDate)
             ->addGroupBy('f.client')
             ->addOrderBy('benefice', 'DESC')
             ->setMaxResults(50)
             ->getQuery()
             ->getResult();
             ;
     }

    /**
     * @return array
     */

    public function topVenteGeneralParProduitParPeriode($produit, $startDate, $endDate): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('c')
            ->select('SUM((c.quantite)) as nbreVente, SUM(c.quantite * c.prixVente) as totalVente, c as commande, SUM(c.quantite * c.prixRevient) as totalRevient, SUM(c.quantite * c.prixVente - c.quantite * c.prixRevient) as benefice')

            ->leftJoin('c.facturation', 'f')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->andWhere('c.product = :produit')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('produit', $produit)
            ->getQuery()
            ->getResult();
            ;
    }

    /**
     * @return array
     */

    public function topVenteGeneralParProduitParPeriodeParLieu($produit, $startDate, $endDate, $lieu): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('c')
            ->select('SUM((c.quantite)) as nbreVente, SUM(c.quantite * c.prixVente) as totalVente, c as commande, SUM(c.quantite * c.prixRevient) as totalRevient, SUM(c.quantite * c.prixVente - c.quantite * c.prixRevient) as benefice')
            ->leftJoin('c.facturation', 'f')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->andWhere('c.product = :produit')
            ->andWhere('f.lieuVente = :lieu')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('produit', $produit)
            ->setParameter('lieu', $lieu)
            ->getQuery()
            ->getResult();
            ;
    }


     public function resteAlivrerParProduit($produit): ?float
     {
         $result = $this->createQueryBuilder('c')
             ->select('SUM((c.quantite - c.quantiteLivre)) as reste')
             ->andWhere('c.product = :produit')
             ->setParameter('produit', $produit)
             ->getQuery()
             ->getSingleResult();
             
        return (float) $result['reste'];
     }


     public function resteAlivrerParProduitParLieu($produit, $lieu): ?float
     {
        $result = $this->createQueryBuilder('c')
             ->select('SUM((c.quantite - c.quantiteLivre)) as reste')
             ->leftJoin('c.facturation', 'f')
             ->andWhere('f.lieuVente = :lieu')
             ->andWhere('c.product = :produit')
             ->setParameter('lieu', $lieu)
             ->setParameter('produit', $produit)
             ->getQuery()
             ->getSingleResult();
             
        return (float) $result['reste'];
     }



     public function nombreDeVentesParPeriodeParProduitGroupeParMois($startDate, $endDate, $produit): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('c')
            ->select('f.dateFacturation as date_facturation')
            ->addSelect('COUNT(c.id) as nbre')
            ->leftJoin('c.facturation', 'f')
            ->Where('c.product = :product')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('product', $produit)
            ->groupBy('date_facturation')
            ->getQuery()
            ->getResult();

            $formattedResults = [];
            $startDateObj = new \DateTime($startDate);
            $endDateObj = $endDate;
            $interval = new \DateInterval('P1M');
            $period = new \DatePeriod($startDateObj, $interval, $endDateObj);

            foreach ($period as $date) {
                $month = $date->format('Y-m');
                $formattedResults[$month] = [
                    'mois' => $month,
                    'nbre' => 0
                ];
            }
            foreach ($results as $result) {
                $month = $result['date_facturation']->format('Y-m');
                if (!isset($formattedResults[$month])) {
                    $formattedResults[$month] = [
                        'mois' => $month,
                        'nbre' => 0
                    ];
                }
                $formattedResults[$month]['nbre'] += (int)$result['nbre'];
            }
        return $formattedResults;

    }

    public function nombreDeVentesParPeriodeParProduitGroupeParAnnees($startDate, $endDate, $produit): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('c')
            ->select('f.dateFacturation as date_facturation')
            ->addSelect('COUNT(c.id) as nbre')
            ->leftJoin('c.facturation', 'f')
            ->Where('c.product = :product')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('product', $produit)
            ->groupBy('date_facturation')
            ->getQuery()
            ->getResult();

            $formattedResults = [];
            $startDateObj = new \DateTime($startDate);
            $endDateObj = $endDate;
            $interval = new \DateInterval('P1M');
            $period = new \DatePeriod($startDateObj, $interval, $endDateObj);

            foreach ($period as $date) {
                $annees = $date->format('Y');
                $formattedResults[$annees] = [
                    'annees' => $annees,
                    'nbre' => 0
                ];
            }
            foreach ($results as $result) {
                $annees = $result['date_facturation']->format('Y');
                if (!isset($formattedResults[$annees])) {
                    $formattedResults[$annees] = [
                        'annees' => $annees,
                        'nbre' => 0
                    ];
                }
                $formattedResults[$annees]['nbre'] += (int)$result['nbre'];
            }
        return $formattedResults;

    }

    public function chiffreAffairesParPeriodeParProduitGroupeParAnnees($startDate, $endDate, $produit): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('c')
            ->select('f.dateFacturation as date_facturation')
            ->addSelect('SUM(c.quantite * c.prixVente)  as nbre')
            ->leftJoin('c.facturation', 'f')
            ->Where('c.product = :product')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('product', $produit)
            ->groupBy('date_facturation')
            ->getQuery()
            ->getResult();

            $formattedResults = [];
            $startDateObj = new \DateTime($startDate);
            $endDateObj = $endDate;
            $interval = new \DateInterval('P1M');
            $period = new \DatePeriod($startDateObj, $interval, $endDateObj);

            foreach ($period as $date) {
                $annees = $date->format('Y');
                $formattedResults[$annees] = [
                    'annees' => $annees,
                    'nbre' => 0
                ];
            }
            foreach ($results as $result) {
                $annees = $result['date_facturation']->format('Y');
                if (!isset($formattedResults[$annees])) {
                    $formattedResults[$annees] = [
                        'annees' => $annees,
                        'nbre' => 0
                    ];
                }
                $formattedResults[$annees]['nbre'] += (int)$result['nbre'];
            }
        return $formattedResults;

    }


    public function chiffreAffairesParProduitGroupeParMois($startDate, $endDate, $produit): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('c')
            ->select('f.dateFacturation as date_facturation')
            ->Where('c.product = :product')
            ->addSelect('SUM(c.quantite * c.prixVente)  as nbre')
            ->leftJoin('c.facturation', 'f')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('product', $produit)
            ->groupBy('date_facturation')
            ->getQuery()
            ->getResult();

            $formattedResults = [];
            $startDateObj = new \DateTime($startDate);
            $endDateObj = $endDate;
            $interval = new \DateInterval('P1M');
            $period = new \DatePeriod($startDateObj, $interval, $endDateObj);

            foreach ($period as $date) {
                $annees = $date->format('Y-m');
                $formattedResults[$annees] = [
                    'mois' => $annees,
                    'nbre' => 0
                ];
            }
            foreach ($results as $result) {
                $annees = $result['date_facturation']->format('Y-m');
                if (!isset($formattedResults[$annees])) {
                    $formattedResults[$annees] = [
                        'mois' => $annees,
                        'nbre' => 0
                    ];
                }
                $formattedResults[$annees]['nbre'] += (int)$result['nbre'];
            }
        return $formattedResults;

    }
}
