<?php

namespace App\Repository;

use App\Entity\Categorie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Categorie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Categorie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Categorie[]    findAll()
 * @method Categorie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategorieRepository extends ServiceEntityRepository
{   

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categorie::class);
        $this->em = $this->getEntityManager()->getConnection();
    }

    // /**
    //  * @return Categorie[] Returns an array of Categorie objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Categorie
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getByUser($user_id)
    {
        $sql = "
            SELECT DISTINCT cat.id, cat.image, cat.description, cat.nom
                FROM categorie as cat
                inner join produit as prod
                inner join restaurant as resto
                inner join user as usr
                WHERE  cat.id = prod.categorie_id
                AND  prod.restaurant_id = resto.id
                AND resto.id = usr.restaurant_id
                AND usr.id = :user_id";
        $commandes = $this->em->prepare($sql);
        $commandes->execute(['user_id'=>$user_id]);
        $commandes = $commandes->fetchAll();
        return $commandes;
    }

}
