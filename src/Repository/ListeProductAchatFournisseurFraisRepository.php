<?php

namespace App\Repository;

use App\Entity\ListeProductAchatFournisseurFrais;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ListeProductAchatFournisseurFrais>
 *
 * @method ListeProductAchatFournisseurFrais|null find($id, $lockMode = null, $lockVersion = null)
 * @method ListeProductAchatFournisseurFrais|null findOneBy(array $criteria, array $orderBy = null)
 * @method ListeProductAchatFournisseurFrais[]    findAll()
 * @method ListeProductAchatFournisseurFrais[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ListeProductAchatFournisseurFraisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ListeProductAchatFournisseurFrais::class);
    }

//    /**
//     * @return ListeProductAchatFournisseurFrais[] Returns an array of ListeProductAchatFournisseurFrais objects
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

//    public function findOneBySomeField($value): ?ListeProductAchatFournisseurFrais
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
