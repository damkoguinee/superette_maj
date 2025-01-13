<?php

namespace App\Repository;

use App\Entity\AbsencesPersonnels;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<AbsencesPersonnels>
 *
 * @method AbsencesPersonnels|null find($id, $lockMode = null, $lockVersion = null)
 * @method AbsencesPersonnels|null findOneBy(array $criteria, array $orderBy = null)
 * @method AbsencesPersonnels[]    findAll()
 * @method AbsencesPersonnels[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbsencesPersonnelsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AbsencesPersonnels::class);
    }

//    /**
//     * @return AbsencesPersonnels[] Returns an array of AbsencesPersonnels objects
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

//    public function findOneBySomeField($value): ?AbsencesPersonnels
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
    public function findSumOfHoursForPersonnel($personnelId, $date): ?int
    {
        // Extraire le mois et l'année de la date fournie
        $year = substr($date, 0, 4);
        $month = substr($date, 5, 2);

        // Créer la date de début et de fin pour le mois donné
        $startDate = new \DateTime("{$year}-{$month}-01");
        $endDate = clone $startDate;
        $endDate->modify('last day of this month');

        return $this->createQueryBuilder('a')
            ->select('SUM(a.heureAbsence) as totalHours')
            ->andWhere('a.personnel = :personnelId')
            ->andWhere('a.dateAbsence BETWEEN :startDate AND :endDate')
            ->setParameters([
                'personnelId' => $personnelId,
                'startDate' => $startDate,
                'endDate' => $endDate,
            ])
            ->getQuery()
            ->getSingleScalarResult();
    }

    
}
