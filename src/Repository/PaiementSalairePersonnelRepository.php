<?php

namespace App\Repository;

use App\Entity\PaiementSalairePersonnel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaiementSalairePersonnel>
 *
 * @method PaiementSalairePersonnel|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaiementSalairePersonnel|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaiementSalairePersonnel[]    findAll()
 * @method PaiementSalairePersonnel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaiementSalairePersonnelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementSalairePersonnel::class);
    }

//    /**
//     * @return PaiementSalairePersonnel[] Returns an array of PaiementSalairePersonnel objects
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

//    public function findOneBySomeField($value): ?PaiementSalairePersonnel
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    
}
