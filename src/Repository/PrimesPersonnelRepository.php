<?php

namespace App\Repository;

use App\Entity\PrimesPersonnel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PrimesPersonnel>
 *
 * @method PrimesPersonnel|null find($id, $lockMode = null, $lockVersion = null)
 * @method PrimesPersonnel|null findOneBy(array $criteria, array $orderBy = null)
 * @method PrimesPersonnel[]    findAll()
 * @method PrimesPersonnel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrimesPersonnelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PrimesPersonnel::class);
    }

//    /**
//     * @return PrimesPersonnel[] Returns an array of PrimesPersonnel objects
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

//    public function findOneBySomeField($value): ?PrimesPersonnel
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    /**
     * @return int|null Returns the sum of hours for the specified personnel
     */
    public function findSumOfPrimesForPersonnel($personnelId, $date): ?int
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
            ->andWhere('a.personnel = :personnelId')
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
