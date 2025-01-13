<?php

namespace App\Repository;

use App\Entity\MouvementCaisse;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<MouvementCaisse>
 *
 * @method MouvementCaisse|null find($id, $lockMode = null, $lockVersion = null)
 * @method MouvementCaisse|null findOneBy(array $criteria, array $orderBy = null)
 * @method MouvementCaisse[]    findAll()
 * @method MouvementCaisse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MouvementCaisseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MouvementCaisse::class);
    }

//    /**
//     * @return MouvementCaisse[] Returns an array of MouvementCaisse objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

    public function verifMouvementPersonnel($collaborateur): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.id')
            ->andWhere('m.saisiePar = :colab')
            // ->andWhere('m.montant != :montant')
            ->setParameter('colab', $collaborateur)
            // ->setParameter('montant', 0)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        ;
    }


   public function findSoldeCaisse($caisse, $devise): ?float
   {
       return $this->createQueryBuilder('m')
           ->select('sum(m.montant) as montant')
           ->andWhere('m.caisse = :val')
           ->andWhere('m.devise = :devise')
           ->setParameter('val', $caisse)
           ->setParameter('devise', $devise)
           ->getQuery()
           ->getSingleScalarResult()
       ;
   }

   public function SoldeCaisseParDeviseParPeriode($caisse, $devise, $startDate, $endDate): ?float
   {
       return $this->createQueryBuilder('m')
           ->select('sum(m.montant) as montant')
           ->andWhere('m.caisse = :val')
           ->andWhere('m.devise = :devise')
           ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
           ->setParameter('val', $caisse)
           ->setParameter('devise', $devise)
           ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
           ->getQuery()
           ->getSingleScalarResult()
       ;
   }

    /**
     * @return array
     */
    public function soldeCaisseGeneralChequesParDeviseParLieuParModePaie($lieu, $devises, $caisses, $mode): array
    {
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'c.id as id_caisse', 'd.nomDevise', 'd.id as id_devise')
            ->leftJoin('m.devise', 'd')
            ->leftJoin('m.caisse', 'c')
            ->leftJoin('m.modePaie', 'p')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('p.designation = :modePaie')
            ->andWhere('c.type = :typeCaisse')
            ->setParameter('lieu', $lieu)
            ->setParameter('modePaie', $mode)
            ->setParameter('typeCaisse', 'caisse')
            ->groupBy('m.devise', 'm.caisse')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            foreach ($caisses as $caisse) {
                $trouve = false;
                foreach ($results as $resultat) {
                    // dd($resultat);
                    if ($resultat['id_devise'] === $devise->getId() && $resultat['id_caisse'] === $caisse->getId()) {
                        $finalResults[] = $resultat;
                        $trouve = true;
                        break;
                    }

                }
                if (!$trouve) {
                    // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                    $finalResults[] = [
                        'solde' => '0.00', 
                        'id_caisse' => $caisse->getId(),
                        'designation' => $caisse->getDesignation(),
                        'nomDevise' => $devise->getNomDevise(),
                        'id_devise' => $devise->getId()
                    ];
                }
            }
        }
        return $finalResults;
    }

    /**
     * @return array
     */
    public function soldeBanquesGeneralParDeviseParLieu($lieu, $devises, $caisses): array
    {
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'c.id as id_caisse', 'd.nomDevise', 'd.id as id_devise')
            ->leftJoin('m.devise', 'd')
            ->leftJoin('m.caisse', 'c')
            ->Where('m.lieuVente = :lieu')
            ->setParameter('lieu', $lieu)
            ->groupBy('m.devise', 'm.caisse')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            foreach ($caisses as $caisse) {
                $trouve = false;
                foreach ($results as $resultat) {
                    if ($resultat['id_devise'] === $devise->getId() && $resultat['id_caisse'] === $caisse->getId()) {
                        $finalResults[] = $resultat;
                        $trouve = true;
                        break;
                    }

                }
                if (!$trouve) {
                    // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                    $finalResults[] = [
                        'solde' => '0.00', 
                        'id_caisse' => $caisse->getId(),
                        'designation' => $caisse->getDesignation(),
                        'nomDevise' => $devise->getNomDevise(),
                        'id_devise' => $devise->getId()
                    ];
                }
            }
        }
        return $finalResults;
    }

    /**
     * @return array
     */
    public function soldeGeneralParDeviseParLieu($lieu, $devises): array
    {
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'd.nomDevise', 'd.id as id_devise')
            ->Where('m.lieuVente = :lieu')
            ->leftJoin('m.devise', 'd')
            ->setParameter('lieu', $lieu)
            ->groupBy('m.devise')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['id_devise'] === $devise->getId()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                $finalResults[] = [
                    'solde' => '0.00', 
                    'nomDevise' => $devise->getNomDevise(),
                    'id_devise' => $devise->getId()
                ];
            }
        }
        return $finalResults;
    }

    /**
     * @return array
     */
   public function mouvementFacturationNotFrais($facturation): array
   {
       return $this->createQueryBuilder('m')
           ->andWhere('m.facturation = :facturation')
           ->andWhere('m.typeMouvement != :type')
           ->setParameter('type', 'frais sup')
           ->setParameter('facturation', $facturation)
           ->getQuery()
            ->getResult()
       ;
   }

//    /**
//      * @return array
//      */
//     public function mouvementCaisseParTypeParPeriodeParLieu($type, $startDate, $endDate, $lieu, $devises = null, $modesPaie = null): array
//     {
//         $endDate = (new \DateTime($endDate))->modify('+1 day');
//         // dd($endDate);
//         $query = $this->createQueryBuilder('m');
//         $results = $query
//             ->select('SUM(m.montant) as montantTotal', 'm.typeMouvement', 'd.nomDevise', 'd.id as id_devise', 'mp.designation as modePaiement', 'mp.id as id_mode_paie')
//             ->leftJoin('m.devise', 'd')
//             ->leftJoin('m.modePaie', 'mp')
//             ->Where('m.lieuVente = :lieu')
//             ->andWhere('m.typeMouvement = :type')
            
//             ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
//             ->setParameter('lieu', $lieu)
//             ->setParameter('type', $type)
//             ->setParameter('startDate', $startDate)
//             ->setParameter('endDate', $endDate);
//         if ($type == 'transfert-versement') {
//             $results->andWhere('m.montant > :montant')
//              ->setParameter('montant', 0);

//         }
//         $results->groupBy('m.devise', 'm.modePaie')
//             ->getQuery()
//              ->getResult()
//         ;

//         $finalResults = [];
//         foreach ($devises as $devise) {
//             foreach ($modesPaie as $modePaie) {
//                 $trouve = false;
//                 foreach ($results as $resultat) {
//                     if ($resultat['id_devise'] === $devise->getId() && $resultat['id_mode_paie'] === $modePaie->getId()) {
//                         $finalResults[] = $resultat;
//                         $trouve = true;
//                         break;
//                     }
//                 }
//                 if (!$trouve) {
//                     // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
//                     $finalResults[] = [
//                         'montantTotal' => '0.00', 
//                         'id_mode_paie' => $modePaie->getId(),
//                         'modePaiement' => $modePaie->getDesignation(),
//                         'nomDevise' => $devise->getNomDevise(),
//                         'id_devise' => $devise->getId(),
//                         'typeMouvement' => $type,
//                     ];
//                 }
//             }
//         }
//         return $finalResults;
//     }

    /**
     * @return array
     */
    public function mouvementCaisseParTypeParPeriodeParLieu($type, $startDate, $endDate, $lieu, $devises = null, $modesPaie = null): array
    {
        // Modifie la date de fin pour inclure toute la journée
        $endDate = (new \DateTime($endDate))->modify('+1 day');

        // Initialisation de la requête
        $query = $this->createQueryBuilder('m');
        $query->select('SUM(m.montant) as montantTotal', 'm.typeMouvement', 'd.nomDevise', 'd.id as id_devise', 'mp.designation as modePaiement', 'mp.id as id_mode_paie')
            ->leftJoin('m.devise', 'd')
            ->leftJoin('m.modePaie', 'mp')
            ->where('m.lieuVente = :lieu')
            ->andWhere('m.typeMouvement = :type')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('type', $type)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        // Exécution de la requête et récupération des résultats
        $results = $query->groupBy('m.devise', 'm.modePaie')
                        ->getQuery()
                        ->getResult();

        // Initialisation de la structure finale des résultats
        $finalResults = [];

        // Boucles pour chaque devise et mode de paiement
        foreach ($devises as $devise) {
            foreach ($modesPaie as $modePaie) {
                $trouve = false;

                // Recherche des résultats correspondant à la devise et au mode de paiement actuels
                foreach ($results as $resultat) {
                    if ($resultat['id_devise'] === $devise->getId() && $resultat['id_mode_paie'] === $modePaie->getId()) {
                        // Si trouvé, on ajoute le résultat à la liste
                        $finalResults[] = $resultat;
                        $trouve = true;
                        break;
                    }
                }

                // Si non trouvé, ajout d'une entrée avec montant à 0
                if (!$trouve) {
                    $finalResults[] = [
                        'montantTotal' => '0.00', 
                        'id_mode_paie' => $modePaie->getId(),
                        'modePaiement' => $modePaie->getDesignation(),
                        'nomDevise' => $devise->getNomDevise(),
                        'id_devise' => $devise->getId(),
                        'typeMouvement' => $type,
                    ];
                }
            }
        }

        return $finalResults;
    }

    /**
     * @return array
     */
    public function mouvementCaisseParTypeParPeriodeParLieuEntree($type, $startDate, $endDate, $lieu, $devises = null, $modesPaie = null): array
    {
        // Modifie la date de fin pour inclure toute la journée
        $endDate = (new \DateTime($endDate))->modify('+1 day');

        // Initialisation de la requête
        $query = $this->createQueryBuilder('m');
        $query->select('SUM(m.montant) as montantTotal', 'm.typeMouvement', 'd.nomDevise', 'd.id as id_devise', 'mp.designation as modePaiement', 'mp.id as id_mode_paie')
            ->leftJoin('m.devise', 'd')
            ->leftJoin('m.modePaie', 'mp')
            ->where('m.lieuVente = :lieu')
            ->andWhere('m.typeMouvement = :type')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->andWhere('m.montant > :montant')
            ->setParameter('lieu', $lieu)
            ->setParameter('type', $type)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('montant', 0);
        

        // Exécution de la requête et récupération des résultats
        $results = $query->groupBy('m.devise', 'm.modePaie')
                        ->getQuery()
                        ->getResult();

        // Initialisation de la structure finale des résultats
        $finalResults = [];

        // Boucles pour chaque devise et mode de paiement
        foreach ($devises as $devise) {
            foreach ($modesPaie as $modePaie) {
                $trouve = false;

                // Recherche des résultats correspondant à la devise et au mode de paiement actuels
                foreach ($results as $resultat) {
                    if ($resultat['id_devise'] === $devise->getId() && $resultat['id_mode_paie'] === $modePaie->getId()) {
                        // Si trouvé, on ajoute le résultat à la liste
                        $finalResults[] = $resultat;
                        $trouve = true;
                        break;
                    }
                }

                // Si non trouvé, ajout d'une entrée avec montant à 0
                if (!$trouve) {
                    $finalResults[] = [
                        'montantTotal' => '0.00', 
                        'id_mode_paie' => $modePaie->getId(),
                        'modePaiement' => $modePaie->getDesignation(),
                        'nomDevise' => $devise->getNomDevise(),
                        'id_devise' => $devise->getId(),
                        'typeMouvement' => $type,
                    ];
                }
            }
        }

        return $finalResults;
    }

    /**
     * @return array
     */
    public function mouvementCaisseParTypeParPeriodeParLieuSortie($type, $startDate, $endDate, $lieu, $devises = null, $modesPaie = null): array
    {
        // Modifie la date de fin pour inclure toute la journée
        $endDate = (new \DateTime($endDate))->modify('+1 day');

        // Initialisation de la requête
        $query = $this->createQueryBuilder('m');
        $query->select('SUM(m.montant) as montantTotal', 'm.typeMouvement', 'd.nomDevise', 'd.id as id_devise', 'mp.designation as modePaiement', 'mp.id as id_mode_paie')
            ->leftJoin('m.devise', 'd')
            ->leftJoin('m.modePaie', 'mp')
            ->where('m.lieuVente = :lieu')
            ->andWhere('m.typeMouvement = :type')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->andWhere('m.montant < :montant')
            ->setParameter('lieu', $lieu)
            ->setParameter('type', $type)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->setParameter('montant', 0);
        

        // Exécution de la requête et récupération des résultats
        $results = $query->groupBy('m.devise', 'm.modePaie')
                        ->getQuery()
                        ->getResult();

        // Initialisation de la structure finale des résultats
        $finalResults = [];

        // Boucles pour chaque devise et mode de paiement
        foreach ($devises as $devise) {
            foreach ($modesPaie as $modePaie) {
                $trouve = false;

                // Recherche des résultats correspondant à la devise et au mode de paiement actuels
                foreach ($results as $resultat) {
                    if ($resultat['id_devise'] === $devise->getId() && $resultat['id_mode_paie'] === $modePaie->getId()) {
                        // Si trouvé, on ajoute le résultat à la liste
                        $finalResults[] = $resultat;
                        $trouve = true;
                        break;
                    }
                }

                // Si non trouvé, ajout d'une entrée avec montant à 0
                if (!$trouve) {
                    $finalResults[] = [
                        'montantTotal' => '0.00', 
                        'id_mode_paie' => $modePaie->getId(),
                        'modePaiement' => $modePaie->getDesignation(),
                        'nomDevise' => $devise->getNomDevise(),
                        'id_devise' => $devise->getId(),
                        'typeMouvement' => $type,
                    ];
                }
            }
        }
        return $finalResults;
    }


    /**
     * @return array
     */
    public function soldeCaisseParDeviseParLieuParType($type, $startDate, $endDate, $lieu, $devises = NULL): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'd.nomDevise', 'd.id as id_devise')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.typeMouvement = :type')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->leftJoin('m.devise', 'd')
            ->setParameter('lieu', $lieu)
            ->setParameter('type', $type)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('m.devise')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['id_devise'] === $devise->getId()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                $finalResults[] = [
                    'solde' => '0.00', 
                    'nomDevise' => $devise->getNomDevise(),
                    'id_devise' => $devise->getId()
                ];
            }
        }
        return $finalResults;
    }

   /**
     * @return array
     */
    public function soldeCaisseParDeviseParLieu($startDate, $endDate, $lieu, $devises): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'd.nomDevise', 'd.id as id_devise')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->leftJoin('m.devise', 'd')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('m.devise')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['id_devise'] === $devise->getId()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                $finalResults[] = [
                    'solde' => '0.00', 
                    'nomDevise' => $devise->getNomDevise(),
                    'id_devise' => $devise->getId()
                ];
            }
        }
        return $finalResults;
    }

   /**
     * @return array
     */
    public function findReceptionTransfertByLieuBySearchPaginated($lieu, $caisse, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('m')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.caisse = :caisse')
            ->andWhere('m.montant > 0 ')
            ->andWhere('m.transfertFond IS NOT NULL')
            ->andWhere('m.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'caisse' => $caisse,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('m.dateSaisie', 'DESC')
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
    public function findReceptionTransfertByLieuPaginated($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('m')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.montant > 0 ')
            ->andWhere('m.transfertFond IS NOT NULL')
            ->andWhere('m.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('m.dateSaisie', 'DESC')
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
    public function soldeCaisseParPeriodeParLieu($startDate, $endDate, $lieu, $devises, $caisses): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'c.id as id_caisse','c.type as type_caisse', 'c.designation', 'd.nomDevise', 'd.id as id_devise')
            ->leftJoin('m.devise', 'd')
            ->leftJoin('m.caisse', 'c')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('m.devise', 'm.caisse')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            foreach ($caisses as $caisse) {
                $trouve = false;
                foreach ($results as $resultat) {
                    if ($resultat['id_devise'] === $devise->getId() && $resultat['id_caisse'] === $caisse->getId()) {
                        $finalResults[] = $resultat;
                        $trouve = true;
                        break;
                    }
                }
                if (!$trouve) {
                    // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                    $finalResults[] = [
                        'solde' => '0.00', 
                        'id_caisse' => $caisse->getId(),
                        'type_caisse' => $caisse->getType(),
                        'designation' => $caisse->getDesignation(),
                        'nomDevise' => $devise->getNomDevise(),
                        'id_devise' => $devise->getId()
                    ];
                }
            }
        }
        return $finalResults;
    }

    /**
     * @return array
     */
    public function soldeCaisseParModePaieParPeriodeParLieu($startDate, $endDate, $modePaie, $lieu, $devises, $caisses): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'c.id as id_caisse','c.type as type_caisse', 'c.designation', 'd.nomDevise', 'd.id as id_devise', 'mp.id as id_modePaie')
            ->leftJoin('m.devise', 'd')
            ->leftJoin('m.modePaie', 'mp')
            ->leftJoin('m.caisse', 'c')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.modePaie = :modePaie')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('modePaie', $modePaie)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('m.devise', 'm.caisse')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];

        foreach ($devises as $devise) {
            foreach ($caisses as $caisse) {
                $trouve = false;
                foreach ($results as $resultat) {
                    if ($resultat['id_devise'] === $devise->getId() && $resultat['id_caisse'] === $caisse->getId()) {
                        $finalResults[] = $resultat;
                        $trouve = true;
                        break;
                    }
                }
                if (!$trouve) {
                    // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                    $finalResults[] = [
                        'solde' => '0.00', 
                        'id_caisse' => $caisse->getId(),
                        'type_caisse' => $caisse->getType(),
                        'designation' => $caisse->getDesignation(),
                        'nomDevise' => $devise->getNomDevise(),
                        'id_devise' => $devise->getId(),
                        'id_modePaie' => 1,
                    ];
                }
            }
        }
        return $finalResults;
    }

    /**
     * @return array
     */
    public function soldeCaisseParLieu($lieu, $devises, $caisses): array
    {
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'c.id as id_caisse', 'c.designation', 'd.nomDevise', 'd.id as id_devise')
            ->leftJoin('m.devise', 'd')
            ->leftJoin('m.caisse', 'c')
            ->Where('m.lieuVente = :lieu')
            ->setParameter('lieu', $lieu)
            ->groupBy('m.devise', 'm.caisse')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            foreach ($caisses as $caisse) {
                $trouve = false;
                foreach ($results as $resultat) {
                    if ($resultat['id_devise'] === $devise->getId() && $resultat['id_caisse'] === $caisse->getId()) {
                        $finalResults[] = $resultat;
                        $trouve = true;
                        break;
                    }
                }
                if (!$trouve) {
                    // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                    $finalResults[] = [
                        'solde' => '0.00', 
                        'id_caisse' => $caisse->getId(),
                        'designation' => $caisse->getDesignation(),
                        'nomDevise' => $devise->getNomDevise(),
                        'id_devise' => $devise->getId()
                    ];
                }
            }
        }
        return $finalResults;
    }

    /**
     * @return array
     */
    public function soldeCaisseParTypeParLieuParDevise($lieu, $devise): array
    {
        return $this->createQueryBuilder('m')
            ->select('SUM(m.montant) as solde, COUNT(m.id) as nbre, m as mouvement')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.devise = :devise')
            ->groupBy('m.typeMouvement')
            ->setParameter('lieu', $lieu)
            ->setParameter('devise', $devise)
            ->getQuery()
            ->getResult();
    }


    /**
     * @return array
     */
    public function soldeCaisseGeneralParDeviseParLieu($lieu, $devises): array
    {
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as solde', 'd.nomDevise', 'd.id as id_devise')
            ->Where('m.lieuVente = :lieu')
            ->leftJoin('m.devise', 'd')
            ->setParameter('lieu', $lieu)
            ->groupBy('m.devise')
            ->orderBy('d.id')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['id_devise'] === $devise->getId()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                $finalResults[] = [
                    'solde' => '0.00', 
                    'nomDevise' => $devise->getNomDevise(),
                    'id_devise' => $devise->getId()
                ];
            }
        }
        return $finalResults;
    }


    /**
     * @return array
     */
    public function soldeCaisseParPeriodeParTypeParLieuParDevise($startDate, $endDate, $lieu, $devise): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('m')
            ->select('SUM(m.montant) as solde, COUNT(m.id) as nbre, m as mouvement')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.devise = :devise')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->groupBy('m.typeMouvement')
            ->setParameter('lieu', $lieu)
            ->setParameter('devise', $devise)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function soldeCaisseParPeriodeParTypeParLieuParDeviseParCaisse($startDate, $endDate, $lieu, $devise, $caisse): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('m')
            ->select('SUM(m.montant) as solde, COUNT(m.id) as nbre, m as mouvement')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.devise = :devise')
            ->andWhere('m.caisse = :caisse')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->groupBy('m.typeMouvement')
            ->setParameter('lieu', $lieu)
            ->setParameter('devise', $devise)
            ->setParameter('caisse', $caisse)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function soldeCaisseParPeriodeParVendeurParLieu($vendeur, $startDate, $endDate, $lieu): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('m')
            ->select('SUM(m.montant) as solde ')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.saisiePar = :vendeur')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->leftJoin('m.caisse', 'c')
            ->addSelect('c.id, c.designation')
            ->groupBy('m.caisse')
            ->setParameter('lieu', $lieu)
            ->setParameter('vendeur', $vendeur)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }


    /**
     * @return array
     */
    public function soldeCaisseParPeriodeParTypeParVendeurParLieu($vendeur, $startDate, $endDate, $lieu): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('m')
            ->select('SUM(m.montant) as solde, COUNT(m.id) as nbre, m as mouvement')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.saisiePar = :vendeur')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->groupBy('m.typeMouvement')
            ->orderBy('solde', 'DESC')
            ->addOrderBy('m.typeMouvement', 'ASC')
            ->setParameter('lieu', $lieu)
            ->setParameter('vendeur', $vendeur)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function compteOperationParPeriodeParLieu($startDate, $endDate, $lieu): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        return $this->createQueryBuilder('m')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function ListeDesChequesCaissesParLieuParDateParPagine($lieu, $caisses, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('m')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.caisse IN (:caisses)')
            ->andWhere('m.modePaie = :mode ')
            ->andWhere('m.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'caisses' => $caisses,
                'mode' => 4,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('m.dateSaisie', 'DESC')
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
    public function ListeDesChequesCaissesParLieuParDateParChequePagine($lieu, $cheque, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('m')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.numeroPaie = :numeroCheque or m.banqueCheque = :numeroCheque')
            ->andWhere('m.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'numeroCheque' => $cheque,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('m.dateSaisie', 'DESC')
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
    public function ListeDesChequesCaissesTraitesParLieuParDatePagine($lieu, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('m')
            ->where('m.lieuVente = :lieu')
            ->andWhere('m.modePaie IN (:mode)')
            ->andWhere('m.etatOperation IN (:etats)')
            ->andWhere('m.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'lieu' => $lieu,
                'mode' => 4,
                'mode' => ['4', '1'], // Utiliser un tableau pour IN
                'etats' => ['traité', 'en attente'], // Utiliser un tableau pour IN
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->orderBy('m.dateSaisie', 'DESC')
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
    public function totauxEncaissementsParPeriodeParLieu($startDate, $endDate, $lieu, $devises): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as montantTotal', 'd.nomDevise', 'd.id as id_devise')
            ->leftJoin('m.devise', 'd')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.montant > 0 ')
            ->andWhere('m.typeMouvement != :type ')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('type', 'facturation')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('m.devise')
            ->getQuery()
             ->getResult()
        ;

        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['id_devise'] === $devise->getId()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                $finalResults[] = [
                    'montantTotal' => '0.00', 
                    'nomDevise' => $devise->getNomDevise(),
                    'id_devise' => $devise->getId()
                ];
            }
        }
        return $finalResults;
    }

    /**
     * @return array
     */
    public function totalEntreesParPeriodeParLieu($startDate, $endDate, $lieu, $devises): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as montantTotal', 'd.nomDevise', 'd.id as id_devise')
            ->leftJoin('m.devise', 'd')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.montant > 0 ')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('m.devise')
            ->getQuery()
             ->getResult()
        ;

        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['id_devise'] === $devise->getId()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                $finalResults[] = [
                    'montantTotal' => '0.00', 
                    'nomDevise' => $devise->getNomDevise(),
                    'id_devise' => $devise->getId()
                ];
            }
        }
        return $finalResults;
    }

    /**
     * @return array
     */
    public function totauxDecaissementsParPeriodeParLieu($startDate, $endDate, $lieu, $devises): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('SUM(m.montant) as montantTotal', 'd.nomDevise', 'd.id as id_devise')
            ->leftJoin('m.devise', 'd')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.montant < 0 ')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->groupBy('m.devise')
            ->getQuery()
             ->getResult()
        ;

        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['id_devise'] === $devise->getId()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                $finalResults[] = [
                    'montantTotal' => '0.00', 
                    'nomDevise' => $devise->getNomDevise(),
                    'id_devise' => $devise->getId()
                ];
            }
        }
        return $finalResults;
    }

    public function soldeCaisseGeneralParTypeGroupeParDevise($caisses): array
    {    
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant', 'c.nomDevise as devise', 'c.id as id_devise')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.caisse IN (:caisses)')
            ->setParameters([
                'caisses' => $caisses,
            ])
            ->addGroupBy('m.devise')
            ->orderBy('m.devise')
            ->getQuery()
            ->getResult();
    }

    public function soldeCaisseGeneralParTypeGroupeParDeviseParLieu($caisses, $lieu): array
    {    
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant', 'c.nomDevise as devise', 'c.id as id_devise')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.lieuVente = :lieu')
            ->andWhere('m.caisse IN (:caisses)')
            ->setParameters([
                'caisses' => $caisses,
                'lieu' => $lieu,
            ])
            ->addGroupBy('m.devise')
            ->orderBy('m.devise')
            ->getQuery()
            ->getResult();
    }


    /**
     * @return array
     */
    public function listeOperationcaisseParLieuParCaisseParDeviseParPeriode($lieu, $caisse, $devise, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $query = $this->createQueryBuilder('m')
            ->Where('m.lieuVente = :lieu')
            ->andWhere('m.caisse = :caisse')
            ->andWhere('m.devise = :devise')
            ->andWhere('m.dateOperation BETWEEN :startDate AND :endDate')
            ->setParameter('lieu', $lieu)
            ->setParameter('devise', $devise)
            ->setParameter('caisse', $caisse)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('m.dateSaisie', 'DESC')
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
