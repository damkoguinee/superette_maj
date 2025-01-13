<?php

namespace App\Repository;

use App\Entity\Caisse;
use App\Entity\PointDeVente;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Caisse>
 *
 * @method Caisse|null find($id, $lockMode = null, $lockVersion = null)
 * @method Caisse|null findOneBy(array $criteria, array $orderBy = null)
 * @method Caisse[]    findAll()
 * @method Caisse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CaisseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Caisse::class);
    }

   /**
    * @return Caisse[] Returns an array of Caisse objects
    */
   public function findCaisseByLieu($lieu_vente): array
   {
       return $this->createQueryBuilder('c')
            ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
           ->where('p.lieuVente = :lieu')
           ->setParameter('lieu', $lieu_vente)
           ->orderBy('c.id', 'ASC')
           ->getQuery()
           ->getResult()
       ;
   }

   /**
    * @return Caisse[] Returns an array of Caisse objects
    */
    public function findCaisseByLieuByType($lieu_vente, $type): array
    {
        return $this->createQueryBuilder('c')
             ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
            ->where('c.type = :type')
            ->andWhere('p.lieuVente = :lieu')
            ->setParameter('type', $type)
            ->setParameter('lieu', $lieu_vente)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * @return Caisse[] Returns an array of Caisse objects
    */
    public function findCaisseGeneralByType($type): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.type = :type')
            ->setParameter('type', $type)
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
    * @return Caisse[] Returns an array of Caisse objects
    */
    public function findCaisseDocumentByLieuByType($lieu_vente, $type): array
    {
        return $this->createQueryBuilder('c')
             ->leftJoin(PointDeVente::class, 'p', 'WITH', 'c.pointDeVente = p.id Or c.pointDeVente is null')
            ->where('c.type = :type')
            ->andWhere('p.lieuVente = :lieu')
            ->andWhere('c.document = :document')
            ->setParameter('type', $type)
            ->setParameter('lieu', $lieu_vente)
            ->setParameter('document', 'actif')
            ->orderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOneCaisseByLieuByType($lieu_vente, $type): ?Caisse
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.pointDeVente', 'p')
            ->andWhere('c.type = :type')
            ->andWhere('p.lieuVente = :lieu OR c.pointDeVente IS NULL')
            ->setParameter('type', $type)
            ->setParameter('lieu', $lieu_vente)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }


   

//    public function findOneBySomeField($value): ?Caisse
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
