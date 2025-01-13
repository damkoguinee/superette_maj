<?php

namespace App\Repository;

use App\Entity\Facturation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Facturation>
 *
 * @method Facturation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Facturation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Facturation[]    findAll()
 * @method Facturation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FacturationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Facturation::class);
    }

//    /**
//     * @return Facturation[] Returns an array of Facturation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Facturation
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
    public function chiffreAffaireParPeriodeParLieu($lieu, $startDate, $endDate): ?float
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('f')
        ->select('SUM(f.totalFacture) as montantTotal')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
        return $query;

    }

    /**
     * @return array
     */
    public function facturationsPayeesParPeriodeParLieu($lieu, $startDate, $endDate): ?float
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('f')
        ->select('SUM(f.montantPaye) as montantPaye')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();

    }

    /**
     * @return array
     */
    public function findFacturationByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('f.dateFacturation', 'DESC')
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
    public function findFacturationNotDelivredByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.etatLivraison != :etat')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'etat' => 'livré',
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('f.dateFacturation', 'DESC')
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
    public function facturationParLieuParPeriode($lieu, $startDate, $endDate): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('f.dateFacturation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function findFacturationByLieuBySearchPaginated($lieu, $client, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.client = :client')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'client' => $client,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('f.dateFacturation', 'DESC')
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
    public function facturationParLieuParClientPaginated($lieu, $client, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.client = :client')
            ->setParameters([
                'lieu' => $lieu,
                'client' => $client,
            ])
            ->orderBy('f.dateFacturation', 'DESC')
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
    public function findFacturationByLieuByPersonnelPaginated($lieu, $personnel, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.saisiePar = :personnel')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'personnel' => $personnel,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('f.dateFacturation', 'DESC')
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
    public function findFacturationNotDelivredByLieuByPersonnelPaginated($lieu, $personnel, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('f')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.etatLivraison != :etat')
            ->andWhere('f.saisiePar = :personnel')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'etat' => 'livré',
                'personnel' => $personnel,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('f.dateFacturation', 'DESC')
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
    public function findFacturationByNumeroPaginated($numero, int $pageEnCours, int $limit): array
    {
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('f')
            ->Where('f.numeroFacture = :numero')
            ->setParameters([
                'numero' => $numero,
            ])
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

    public function nombreDeVentesParPeriode($startDate, $endDate): ?int
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $result = $this->createQueryBuilder('f')
            ->select('COUNT(f.id) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    public function nombreDeVentesParPeriodeParLieu($startDate, $endDate, $lieu): ?int
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $result = $this->createQueryBuilder('f')
            ->select('COUNT(f.id) as nbre')
            ->Where('f.lieuVente = :lieu')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }

    public function nombreDeVentesParPeriodeGroupeParMois($startDate, $endDate): ?array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->addSelect('COUNT(f.id) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
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

    public function nombreDeVentesParPeriodeParLieuGroupeParMois($startDate, $endDate, $lieu): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->Where('f.lieuVente = :lieu')
            ->addSelect('COUNT(f.id) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('lieu', $lieu)
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

    public function nombreDeVentesParPeriodeGroupeParAnnees($startDate, $endDate): ?array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->addSelect('COUNT(f.id) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
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

    public function nombreDeVentesParPeriodeParLieuGroupeParAnnees($startDate, $endDate, $lieu): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->Where('f.lieuVente = :lieu')
            ->addSelect('COUNT(f.id) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('lieu', $lieu)
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

    


    public function chiffreAffairesParPeriodeGroupeParAnnees($startDate, $endDate): ?array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->addSelect('SUM(f.totalFacture) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
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

    

    public function chiffreAffairesParPeriodeParLieuGroupeParAnnees($startDate, $endDate, $lieu): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->Where('f.lieuVente = :lieu')
            ->addSelect('SUM(f.totalFacture) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('lieu', $lieu)
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

    


    public function chiffreAffairesGroupeParMois($startDate, $endDate): ?array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->addSelect('SUM(f.totalFacture) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
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

    public function chiffreAffairesParLieuGroupeParMois($startDate, $endDate, $lieu): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->Where('f.lieuVente = :lieu')
            ->addSelect('SUM(f.totalFacture) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('lieu', $lieu)
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

    public function nombreDeVentesParPeriodeParClientGroupeParMois($startDate, $endDate, $client): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->Where('f.client = :client')
            ->addSelect('COUNT(f.id) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('client', $client)
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

    public function nombreDeVentesParPeriodeParClientGroupeParAnnees($startDate, $endDate, $client): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->Where('f.client = :client')
            ->addSelect('COUNT(f.id) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('client', $client)
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

    public function chiffreAffairesParPeriodeParClientGroupeParAnnees($startDate, $endDate, $client): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->Where('f.client = :client')
            ->addSelect('SUM(f.totalFacture) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('client', $client)
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


    public function chiffreAffairesParClientGroupeParMois($startDate, $endDate, $client): ?array
    {

        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $results = $this->createQueryBuilder('f')
            ->select('f.dateFacturation as date_facturation')
            ->Where('f.client = :client')
            ->addSelect('SUM(f.totalFacture) as nbre')
            ->andWhere('f.dateFacturation BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('client', $client)
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

    

    public function findMaxId(): ?int
    {
        $result = $this->createQueryBuilder('f')
            ->select('MAX(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
        return $result;
    }
}
