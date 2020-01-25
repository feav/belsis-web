<?php

namespace App\Repository;

use App\Entity\Commande;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Commande|null find($id, $lockMode = null, $lockVersion = null)
 * @method Commande|null findOneBy(array $criteria, array $orderBy = null)
 * @method Commande[]    findAll()
 * @method Commande[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommandeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commande::class);
        $this->em = $this->getEntityManager()->getConnection();
    }

    // /**
    //  * @return Commande[] Returns an array of Commande objects
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
    public function findOneBySomeField($value): ?Commande
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getCommandeAll()
    {
        $sql = "
            SELECT DISTINCT cmd.id, cmd.code, cmd.date, cmd.etat
                FROM commande as cmd
                inner join commande_produit as cmd_prod
                inner join produit as prod
                inner join restaurant as resto
                WHERE  cmd.id = cmd_prod.commande_id
                AND  cmd_prod.produit_id = prod.id
                AND prod.restaurant_id = resto.id
                ORDER BY cmd.date DESC";
        $commandes = $this->em->prepare($sql);
        $commandes->execute();
        $commandes = $commandes->fetchAll();

        return $commandes;
    }

    public function getCommandeAllByRestaurant($idrestaurant)
    {
        $sql = "
            SELECT DISTINCT cmd.id, cmd.code, cmd.date, cmd.etat
                FROM commande as cmd
                inner join commande_produit as cmd_prod
                inner join produit as prod
                inner join restaurant as resto
                WHERE  cmd.id = cmd_prod.commande_id
                AND  cmd_prod.produit_id = prod.id
                AND prod.restaurant_id = resto.id
                AND resto.id = :idrestaurant
                ORDER BY cmd.date DESC";
        $commandes = $this->em->prepare($sql);
        $commandes->execute(['idrestaurant'=>$idrestaurant]);
        $commandes = $commandes->fetchAll();
        return $commandes;
    }

    public function getByCuisinier($cuisinier_id)
    {
        $sql = "
            SELECT cmd.montant
                FROM commande as cmd
                WHERE cmd.cuisinier = :cuisinier_id
                AND cmd.etat != :etat";
        $commandes = $this->em->prepare($sql);
        $commandes->execute(['cuisinier_id'=>$cuisinier_id, 'etat'=>"corbeille"]);
        $commandes = $commandes->fetchAll();
        return $commandes;
    }
    public function getByServeur($user_id)
    {
        $sql = "
            SELECT cmd.montant
                FROM commande as cmd
                WHERE  cmd.user_id = :user_id
                AND cmd.etat != :etat";
        $commandes = $this->em->prepare($sql);
        $commandes->execute(['user_id'=>$user_id, 'etat'=>"corbeille"]);
        $commandes = $commandes->fetchAll();
        return $commandes;
    }

    public function getCommandeDetail($user_id)
    {
        $sql = "
            SELECT DISTINCT cmd.id, cmd.code, cmd.date, cmd.etat, prod.nom as produit_nom, resto.nom as restaurant_nom
                FROM commande as cmd
                inner join commande_produit as cmd_prod
                inner join produit as prod
                inner join restaurant as resto
                WHERE  cmd.id = cmd_prod.commande_id
                AND  cmd_prod.produit_id = prod.id
                AND prod.restaurant_id = resto.id
                AND cmd.id = :idCommande
                ORDER BY cmd.date DESC";
        $commandes = $this->em->prepare($sql);
        $commandes->execute(['idCommande'=>$idCommande]);
        $commandes = $commandes->fetch();

        return $commandes;
    }

    public function getCommandeRestaurant($idrestaurant)
    {
        $sql = "
            SELECT COUNT(cmd.id) as nbrCommande
                FROM commande as cmd
                inner join commande_produit as cmd_prod
                inner join produit as prod
                inner join restaurant as resto
                WHERE  cmd.id = cmd_prod.commande_id
                AND  cmd_prod.produit_id = prod.id
                AND prod.restaurant_id = resto.id
                AND resto.id = :idrestaurant
                ORDER BY cmd.date DESC";
        $nbrCommande = $this->em->prepare($sql);
        $nbrCommande->execute(['idrestaurant'=>$idrestaurant]);
        $nbrCommande = $nbrCommande->fetch();

        return $nbrCommande['nbrCommande'];
    }

    public function getByShopActivityByDate($restaurant, $dateStart, $dateEnd)
    {
        return $this->createQueryBuilder('c')
            ->where('c.restaurant = :restaurant')
            ->andWhere('c.date >= :dateStart')
            ->andWhere('c.date <= :dateEnd')
            //->orderBy('c.date', 'ASC')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->getQuery()
            ->execute();
    }    
    public function getByShopActivityByDatePaye($restaurant, $dateStart, $dateEnd)
    {
        return $this->createQueryBuilder('c')
            ->where('c.restaurant = :restaurant')
            //->andWhere('c.etat = :etat')
            ->andWhere('c.date >= :dateStart')
            ->andWhere('c.date <= :dateEnd')
            //->orderBy('c.date', 'ASC')
            ->setParameter('restaurant', $restaurant)
            //->setParameter('etat', "paye")
            ->setParameter('dateStart', $dateStart)
            ->setParameter('dateEnd', $dateEnd)
            ->getQuery()
            ->execute();
    }
}
