<?php

namespace App\Repository;

use App\Entity\SortieCaisse;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SortieCaisse|null find($id, $lockMode = null, $lockVersion = null)
 * @method SortieCaisse|null findOneBy(array $criteria, array $orderBy = null)
 * @method SortieCaisse[]    findAll()
 * @method SortieCaisse[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SortieCaisseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SortieCaisse::class);
    }

    // /**
    //  * @return SortieCaisse[] Returns an array of SortieCaisse objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SortieCaisse
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
