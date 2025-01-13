<?php

namespace App\Repository;

use App\Entity\AvanceSalaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AvanceSalaire>
 *
 * @method AvanceSalaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method AvanceSalaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method AvanceSalaire[]    findAll()
 * @method AvanceSalaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvanceSalaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AvanceSalaire::class);
    }

//    /**
//     * @return AvanceSalaire[] Returns an array of AvanceSalaire objects
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

//    public function findOneBySomeField($value): ?AvanceSalaire
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
    /**
     * @return int|null Returns the sum of hours for the specified personnel
     */
    public function findSumOfAvanceForPersonnel($personnelId, $date): ?int
    {
        // Extraire le mois et l'année de la date fournie
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        // Créer la date de début et de fin pour le mois donné
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');

        return $this->createQueryBuilder('a')
            ->select('SUM(a.montant) as totalMontant')
            ->andWhere('a.user = :personnelId')
            ->andWhere('a.periode BETWEEN :startDate AND :endDate')
            ->setParameters([
                'personnelId' => $personnelId,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }
}
