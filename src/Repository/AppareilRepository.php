<?php

namespace App\Repository;

use App\Entity\Appareil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Appareil|null find($id, $lockMode = null, $lockVersion = null)
 * @method Appareil|null findOneBy(array $criteria, array $orderBy = null)
 * @method Appareil[]    findAll()
 * @method Appareil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppareilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appareil::class);
    }

    // /**
    //  * @return Appareil[] Returns an array of Appareil objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Appareil
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
