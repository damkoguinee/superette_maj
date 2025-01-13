<?php

namespace App\Repository;

use App\Entity\ListeProductAchatFournisseurMultiple;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListeProductAchatFournisseurMultiple>
 *
 * @method ListeProductAchatFournisseurMultiple|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListeProductAchatFournisseurMultiple|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListeProductAchatFournisseurMultiple[]    findAll()
 * @method ListeProductAchatFournisseurMultiple[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListeProductAchatFournisseurMultipleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeProductAchatFournisseurMultiple::class);
    }

//    /**
//     * @return ListeProductAchatFournisseurMultiple[] Returns an array of ListeProductAchatFournisseurMultiple objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('l.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?ListeProductAchatFournisseurMultiple
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
