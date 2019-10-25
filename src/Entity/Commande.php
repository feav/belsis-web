<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommandeRepository")
 */
class Commande
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ModePaiement", inversedBy="commandes")
     */
    private $modepaiement;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="commandes")
     */
    private $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Table", inversedBy="commandes")
     */
    private $table;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="commandes")
     */
    private $user;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Produit", inversedBy="commandes")
     */
    private $produit;

    public function __construct()
    {
        $this->produit = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    public function getEtat()
    {
        return $this->etat;
    }

    public function setEtat($etat)
    {
        $this->etat = $etat;

        return $this;
    }

    public function getModepaiement()
    {
        return $this->modepaiement;
    }

    public function setModepaiement($modepaiement)
    {
        $this->modepaiement = $modepaiement;

        return $this;
    }

    public function getRestaurant()
    {
        return $this->restaurant;
    }

    public function setRestaurant($restaurant)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Produit[]
     */
    public function getProduit()
    {
        return $this->produit;
    }

    public function addProduit($produit)
    {
        if (!$this->produit->contains($produit)) {
            $this->produit[] = $produit;
        }

        return $this;
    }

    public function removeProduit($produit)
    {
        if ($this->produit->contains($produit)) {
            $this->produit->removeElement($produit);
        }

        return $this;
    }

    public function __toString(){
        return $this->nom;
    }
}
