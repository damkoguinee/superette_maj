<?php

namespace App\Repository;

use App\Entity\Personnel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Personnel>
 *
 * @method Personnel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Personnel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Personnel[]    findAll()
 * @method Personnel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonnelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Personnel::class);
    }

//    /**
//     * @return Personnel[] Returns an array of Personnel objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Personnel
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findPersonnelByTypeBySearchByLieu($type1, $type2, $lieu, $personnel): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('u.typeUser = :type')
            // ->andWhere('p.typeClient = :type1 or c.typeClient = :type2 ')
            ->andWhere('u.id = :client')
            ->andWhere('u.lieuVente = :lieu')
            ->setParameter('type', 'personnel')
            // ->setParameter('type1', $type1)
            // ->setParameter('type2', $type2)
            ->setParameter('client', $personnel)
            ->setParameter('lieu', $lieu)
            ->orderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    public function findPersonnelByTypeByLieu($type1, $type2, $lieu): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->where('u.typeUser = :type')
            // ->andWhere('p.typeClient = :type1 or c.typeClient = :type2 ')
            ->andWhere('u.lieuVente = :lieu')
            ->setParameter('type', 'personnel')
            // ->setParameter('type1', $type1)
            // ->setParameter('type2', $type2)
            ->setParameter('lieu', $lieu)
            ->orderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    /**
     
     * @return array
     */
    public function findUsersNotInPaiementsForPeriod($date = null): array
    {
        // Extraire le mois et l'année de la date fournie
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        // Créer la date de début et de fin pour le mois donné
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('u.paiementSalairePersonnels', 's', 'WITH', 's.periode BETWEEN :date1 AND :date2')
            ->andWhere('s.id IS NULL')
            ->setParameter('date1', $startDate)
            ->setParameter('date2', $endDate)
            ->addOrderBy('u.prenom', 'ASC') // Tri par prénom
            ->addOrderBy('u.nom', 'ASC')    // Ensuite, tri par nom
            ->getQuery()
            ->getResult();
    }

    /**
     
     * @return array
     */
    public function findUsersNotInPaiementsForPeriodByLieu($date, $lieu_vente): array
    {
        // Extraire le mois et l'année de la date fournie
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        // Créer la date de début et de fin pour le mois donné
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');
        return $this->createQueryBuilder('p')
            ->leftJoin('p.user', 'u')
            ->leftJoin('u.paiementSalairePersonnels', 's', 'WITH', 's.periode BETWEEN :date1 AND :date2')
            ->andWhere('s.id IS NULL')
            ->andWhere('u.lieuVente = :lieu')
            ->setParameter('lieu', $lieu_vente)
            ->setParameter('date1', $startDate)
            ->setParameter('date2', $endDate)
            ->addOrderBy('u.prenom', 'ASC') // Tri par prénom
            ->addOrderBy('u.nom', 'ASC')    // Ensuite, tri par nom
            ->getQuery()
            ->getResult();
    }
}
