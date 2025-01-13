<?php

namespace App\Repository;

use App\Entity\ProformatFraisSup;
use App\Entity\ProformatProduct;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ProformatProduct>
 *
 * @method ProformatProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProformatProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProformatProduct[]    findAll()
 * @method ProformatProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProformatProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProformatProduct::class);
    }

//    /**
//     * @return ProformatProduct[] Returns an array of ProformatProduct objects
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

//    public function findOneBySomeField($value): ?ProformatProduct
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

/**
     * @return array
     */
    public function findAllCommand($proformat): array
    {
        $result = [];
        $query = $this->createQueryBuilder('c')
            ->select('c as commande')
            ->where('c.proformat = :proformat ')
            ->setParameter('proformat', $proformat)
            ->orderBy('c.id', 'ASC');
        $data= $query->getQuery()
                    ->getResult();
        $result['data'] = $data; 
        
       // Query for FactureFraisSup
        $factureFraisSupRepository = $this->_em->getRepository(ProformatFraisSup::class);
        $queryFrais = $factureFraisSupRepository->createQueryBuilder('f')
            ->where('f.proformat = :proformat')
            ->setParameter('proformat', $proformat)
            ->getQuery()
            ->getResult();
        $result['frais'] = $queryFrais;
        return $result;
    }

    
}
