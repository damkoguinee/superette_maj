<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Client>
 *
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

//    /**
//     * @return Client[] Returns an array of Client objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(100)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Client
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findAllClientByLieu($lieu): array
    {
        $type = "client";
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->where('u.typeUser = :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('c.rattachement = :lieu')
            ->setParameter('type', $type)
            ->setParameter('statut', 'actif')
            ->setParameter('lieu', $lieu)
            ->orderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    public function findAllClientInactifByLieu($lieu): array
    {
        $type = "client";
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->where('u.typeUser = :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('c.rattachement = :lieu')
            ->setParameter('type', $type)
            ->setParameter('statut', 'inactif')
            ->setParameter('lieu', $lieu)
            ->orderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    public function findUserSearchByLieu($value, $lieu): array
    {
        $type = "client";
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->where('u.typeUser = :type')
            ->andWhere('c.rattachement = :lieu')
            ->andWhere('u.statut = :statut')
            ->andWhere('u.prenom LIKE :val Or u.nom LIKE :val Or u.telephone LIKE :val')
            ->setParameter('type', $type)
            ->setParameter('statut', 'actif')
            ->setParameter('lieu', $lieu)
            ->setParameter('val', '%' . $value . '%')
            ->setMaxResults(100)
            ->getQuery()
            ->getResult();
    }
    

    public function findClientByTypeByLieu($type1, $type2, $lieu): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->where('u.typeUser = :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('c.typeClient = :type1 or c.typeClient = :type2 ')
            ->andWhere('c.rattachement = :lieu')
            ->setParameter('type', 'client')
            ->setParameter('statut', 'actif')
            ->setParameter('type1', $type1)
            ->setParameter('type2', $type2)
            ->setParameter('lieu', $lieu)
            ->orderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    public function listeDesClientsGeneralParType($type1, $type2): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->where('u.typeUser = :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('c.typeClient = :type1 or c.typeClient = :type2 ')
            ->setParameter('type', 'client')
            ->setParameter('statut', 'actif')
            ->setParameter('type1', $type1)
            ->setParameter('type2', $type2)
            ->orderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    public function findClientSearchByTypeByLieu($type1, $type2, $lieu, $client): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->where('u.typeUser = :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('c.typeClient = :type1 or c.typeClient = :type2 ')
            ->andWhere('u.id = :client')
            ->andWhere('c.rattachement = :lieu')
            ->setParameter('statut', 'actif')
            ->setParameter('type', 'client')
            ->setParameter('type1', $type1)
            ->setParameter('type2', $type2)
            ->setParameter('client', $client)
            ->setParameter('lieu', $lieu)
            ->orderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }

    public function findClientSearchByTypeByLieuByRegion($type1, $type2, $lieu, $region): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.user', 'u')
            ->where('u.typeUser = :type')
            ->andWhere('u.statut = :statut')
            ->andWhere('c.typeClient = :type1 or c.typeClient = :type2 ')
            ->andWhere('u.region = :region')
            ->andWhere('c.rattachement = :lieu')
            ->setParameter('statut', 'actif')
            ->setParameter('type', 'client')
            ->setParameter('type1', $type1)
            ->setParameter('type2', $type2)
            ->setParameter('region', $region)
            ->setParameter('lieu', $lieu)
            ->orderBy('u.prenom')
            ->getQuery()
            ->getResult();
    }
}
