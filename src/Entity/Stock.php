<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StockRepository")
 */
class Stock
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="float")
     */
    private $quantite;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="stocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $restaurant;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Produit", mappedBy="stock")
     */
    private $produits;

    public function __construct()
    {
        $this->produits = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getNom()
    {
        return $this->nom;
    }

    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    public function getQuantite()
    {
        return $this->quantite;
    }

    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;

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

    /**
     * @return Collection|Produit[]
     */
    public function getProduits()
    {
        return $this->produits;
    }

    public function addProduit($produit)
    {
        if (!$this->produits->contains($produit)) {
            $this->produits[] = $produit;
            $produit->addStock($this);
        }

        return $this;
    }

    public function removeProduit($produit)
    {
        if ($this->produits->contains($produit)) {
            $this->produits->removeElement($produit);
            $produit->removeStock($this);
        }

        return $this;
    }

    public function __toString(){
        return $this->nom;
    }
}
