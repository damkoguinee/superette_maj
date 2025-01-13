<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\MouvementCollaborateur;
use App\Entity\Personnel;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<MouvementCollaborateur>
 *
 * @method MouvementCollaborateur|null find($id, $lockMode = null, $lockVersion = null)
 * @method MouvementCollaborateur|null findOneBy(array $criteria, array $orderBy = null)
 * @method MouvementCollaborateur[]    findAll()
 * @method MouvementCollaborateur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MouvementCollaborateurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MouvementCollaborateur::class);
    }

//    /**
//     * @return MouvementCollaborateur[] Returns an array of MouvementCollaborateur objects
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

//    public function findOneBySomeField($value): ?MouvementCollaborateur
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function verifMouvement($collaborateur): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.id')
            ->andWhere('m.collaborateur = :colab')
            // ->andWhere('m.montant != :montant')
            ->setParameter('colab', $collaborateur)
            // ->setParameter('montant', 0)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        ;
    }

    public function verifMouvementPersonnel($collaborateur): array
    {
        return $this->createQueryBuilder('m')
            ->select('m.id')
            ->andWhere('m.traitePar = :colab')
            // ->andWhere('m.montant != :montant')
            ->setParameter('colab', $collaborateur)
            // ->setParameter('montant', 0)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        ;
    }

    public function findSoldeCollaborateur($collaborateur, $devise = null): array
    {
        $query = $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant', 'c.nomDevise as devise')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.collaborateur = :colab')
            ->setParameter('colab', $collaborateur);

        if ($devise) {
            $query->andWhere('m.devise = :devise')
                ->setParameter('devise', $devise);
        }

        return $query->groupBy('m.devise')
            ->getQuery()
            ->getResult();
    }

    public function soldeCollaborateurParDevise($collaborateur, $devise): float
    {
        $query = $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.collaborateur = :colab')
            ->setParameter('colab', $collaborateur)
            ->andWhere('m.devise = :devise')
            ->setParameter('devise', $devise);
        

        return $query->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array
     */
    public function SoldeDetailByCollaborateurByDeviseByDate($collaborateur, $devise, $startDate, $endDate, int $pageEnCours, int $limit): array
    {
        $endDate = (new \DateTime($endDate))->modify('+1 day');
        $limit = abs($limit);
        $result = [];
        $query = $this->createQueryBuilder('m')
            ->andWhere('m.collaborateur = :collab')
            ->andWhere('m.devise = :devise')
            ->andWhere('m.dateSaisie BETWEEN :startDate AND :endDate')
            ->setParameters([
                'collab' => $collaborateur,
                'devise' => $devise,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->addOrderBy('m.dateOperation', 'ASC')  // Premièrement ordonné par date
            ->addOrderBy('m.id', 'ASC')  // Ensuite ordonné par ID
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
        ;
    }

    /**
     * @return int|null
     */
    public function sumMontantBeforeStartDate($collaborateur, $devise, $startDate): ?int
    {
        $query = $this->createQueryBuilder('m')
            ->select('sum(m.montant) as totalMontant')
            ->andWhere('m.collaborateur = :collab')
            ->andWhere('m.devise = :devise')
            ->andWhere('m.dateSaisie < :startDate')
            ->setParameters([
                'collab' => $collaborateur,
                'devise' => $devise,
                'startDate' => $startDate,
            ])
            ->getQuery()
            ->getSingleScalarResult();
        return $query;
    }

    public function findSoldeCompteCollaborateur($collaborateur, $devises): array
    {
        $query = $this->createQueryBuilder('m');        
        $results = $query
            ->select('sum(m.montant) as montant', 'd.nomDevise as devise', 'd.id as id_devise', 'max(m.dateOperation) as derniereOperation')
            ->leftJoin('m.devise', 'd')
            ->andWhere('m.collaborateur = :colab')
            ->setParameter('colab', $collaborateur)
            ->addGroupBy('d.nomDevise')
            ->getQuery()
            ->getResult();

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['devise'] === $devise->getNomDevise()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise n'est pas trouvée dans les résultats, ajouter une entrée avec un montant de zéro
                $finalResults[] = ['montant' => '0.00', 'devise' => $devise->getNomDevise(), 'id_devise' => $devise->getId()];
            }
        }

        return $finalResults;
    }

    public function findSoldeGeneralByType($type1, $type2, $lieu_vente, $devises = null): array
    {
        if ($type1 == 'personnel' and $type2 == 'personnel') {
            $query = $this->createQueryBuilder('m');
            $results = $query
                ->select('sum(m.montant) as montant' , 'd.nomDevise as devise')
                ->leftJoin('m.devise', 'd')
                ->leftJoin('m.collaborateur', 'u')
                ->leftJoin(Personnel::class, 'p', 'WITH', 'p.user = u.id ')
                ->andWhere('u.statut = :statut')
                ->andWhere('u.typeUser = :type1 ')
                ->andWhere('m.lieuVente= :lieu')
                ->setParameter('statut', 'actif')
                ->setParameter('lieu', $lieu_vente)
                ->setParameter('type1', 'personnel')
                ->addGroupBy('m.devise')
                ->getQuery()
                ->getResult()
    
            ;
        }else{

            $query = $this->createQueryBuilder('m');
            $results = $query
                ->select('sum(m.montant) as montant' , 'd.nomDevise as devise')
                ->leftJoin('m.devise', 'd')
                ->leftJoin('m.collaborateur', 'u')
                ->leftJoin(Client::class, 'c', 'WITH', 'c.user = u.id ')
                ->andWhere('u.statut = :statut')
                ->andWhere('c.rattachement= :lieu')
                ->andWhere('c.typeClient = :type1 or c.typeClient = :type2 ')
                ->setParameter('statut', 'actif')
                ->setParameter('lieu', $lieu_vente)
                ->setParameter('type1', $type1)
                ->setParameter('type2', $type2)
                ->addGroupBy('m.devise')
                ->getQuery()
                ->getResult()
    
            ;
        }

        // Créer un tableau pour stocker les résultats finaux
        $finalResults = [];
        foreach ($devises as $devise) {
            $trouve = false;
            foreach ($results as $resultat) {
                if ($resultat['devise'] === $devise->getNomdevise()) {
                    $finalResults[] = $resultat;
                    $trouve = true;
                    break;
                }
            }
            if (!$trouve) {
                // Si la devise et la caisse ne sont pas trouvées dans les résultats, ajouter une entrée avec un solde de zéro
                $finalResults[] = [
                    'montant' => '0.00', 
                    'devise' => $devise->getNomDevise()
                ];
            }
        }
        return $finalResults;
    }

    public function findAncienSoldeCollaborateur($collaborateur, $dateOp): array
    {
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant' , 'c.nomDevise as devise')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.collaborateur = :colab')
            ->andWhere('m.dateOperation < :dateOp')
            ->setParameter('colab', $collaborateur)
            ->setParameter('dateOp', $dateOp)
            ->addGroupBy('m.devise')
            ->orderBy('m.devise')
            ->getQuery()
            ->getResult()

        ;
    }

    public function listeSoldeGeneralGroupeParDeviseParCollaborateurParAnnee($anneeOp): array
    {
        $dateDebutAnnee = new \DateTime($anneeOp . '-01-01');
        $dateFinAnnee = new \DateTime($anneeOp . '-12-31');
    
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant', 'c.nomDevise as devise', 'c.id as id_devise')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.dateOperation BETWEEN :dateDebut AND :dateFin')
            ->setParameter('dateDebut', $dateDebutAnnee)
            ->setParameter('dateFin', $dateFinAnnee)
            ->addGroupBy('m.devise, m.collaborateur')
            ->orderBy('m.devise')
            ->getQuery()
            ->getResult();
    }

    public function listeSoldeGeneralGroupeParDeviseParCollaborateurParAnneeParLieu($anneeOp, $lieu): array
    {
        $dateDebutAnnee = new \DateTime($anneeOp . '-01-01');
        $dateFinAnnee = new \DateTime($anneeOp . '-12-31');
    
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as montant', 'c.nomDevise as devise', 'c.id as id_devise')
            ->leftJoin('m.devise', 'c')
            ->andWhere('m.lieuVente = :lieu')
            ->andWhere('m.dateOperation BETWEEN :dateDebut AND :dateFin')
            ->setParameter('lieu', $lieu)
            ->setParameter('dateDebut', $dateDebutAnnee)
            ->setParameter('dateFin', $dateFinAnnee)
            ->addGroupBy('m.devise, m.collaborateur')
            ->orderBy('m.devise')
            ->getQuery()
            ->getResult();
    }


    public function findSoldeCompteCollaborateurInactif($collaborateur, $limit)
    {
        $dateLimite = new \DateTime();
        $dateLimite->modify(-$limit.' days');
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('sum(m.montant) as montant', 'd.nomDevise as devise', 'max(m.dateOperation) as derniereOperation')
            ->leftJoin('m.devise', 'd')
            ->andWhere('m.collaborateur = :colab')
            ->andWhere('m.devise = 1')
            ->setParameter('colab', $collaborateur)
            ->groupBy('d.nomDevise')
            ->having('sum(m.montant) < 0')
            ->andHaving('sum(m.montant) != 0')
            ->getQuery()
            ->getResult();

        $finalResults = [];

        foreach ($results as $result) {
            $derniereOperation = new \DateTime($result['derniereOperation']);
            $difference = $derniereOperation->diff($dateLimite);
            // dd($derniereOperation, $dateLimite, $limit, $difference);
            if ($difference->days >= $limit) {
                $finalResults[] = $result;
            }

        }
    
        return $finalResults;
    }

    public function comptesInactif($collaborateur, $limit)
    {
        // Créer une date limite basée sur le nombre de jours $limit
        $dateLimite = new \DateTime();
        $dateLimite->modify('-'.$limit.' days');

        // Construire la requête
        $query = $this->createQueryBuilder('m');
        $results = $query
            ->select('sum(m.montant) as montant', 'd.nomDevise as devise', 'max(m.dateOperation) as derniereOperation')
            ->leftJoin('m.devise', 'd')
            ->andWhere('m.collaborateur = :colab')
            ->andWhere('m.devise = 1') // Si '1' est l'ID d'une devise particulière
            ->andWhere('m.dateOperation <= :dateLimite') // Filtrer les opérations avant la date limite
            ->setParameter('colab', $collaborateur)
            ->setParameter('dateLimite', $dateLimite) // Paramètre pour la date limite
            ->groupBy('d.nomDevise')
            ->having('sum(m.montant) < 0') // Conditions sur le solde
            ->andHaving('sum(m.montant) != 0')
            ->getQuery()
            ->getResult();

        // Retourner les résultats directement, car les résultats sont déjà filtrés par la requête SQL
        return $results;
    }



    public function soldeCollaborateurParLieuGroupeParDevise($lieu_vente): array
    {
        return $this->createQueryBuilder('m')
            ->select('sum(m.montant) as totalMontant', 'm as mouvement')
            ->andWhere('m.lieuVente = :lieu')
            ->setParameters([
                'lieu' => $lieu_vente,
                
            ])
            ->groupBy('m.collaborateur')
            ->addGroupBy('m.devise')
            ->getQuery()
            ->getResult();
    }
}
