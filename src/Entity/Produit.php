<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProduitRepository")
 */
class Produit
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
     * @ORM\Column(type="integer")
     */
    private $prix;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="produits")
     */
    private $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Categorie", inversedBy="produits")
     * @ORM\JoinColumn(nullable=false,onDelete="CASCADE")
     */
    private $categorie;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Stock", inversedBy="produits")
     */
    private $stock;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Commande", mappedBy="produit")
     */
    private $commandes;

    public function __construct()
    {
        $this->stock = new ArrayCollection();
        $this->commandes = new ArrayCollection();
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

    public function getPrix()
    {
        return $this->prix;
    }

    public function setPrix($prix)
    {
        $this->prix = $prix;

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

    public function getCategorie()
    {
        return $this->categorie;
    }

    public function setCategorie($categorie)
    {
        $this->categorie = $categorie;

        return $this;
    }

    /**
     * @return Collection|Stock[]
     */
    public function getStock()
    {
        return $this->stock;
    }

    public function addStock($stock)
    {
        if (!$this->stock->contains($stock)) {
            $this->stock[] = $stock;
        }

        return $this;
    }

    public function removeStock($stock)
    {
        if ($this->stock->contains($stock)) {
            $this->stock->removeElement($stock);
        }

        return $this;
    }

    /**
     * @return Collection|Commande[]
     */
    public function getCommandes()
    {
        return $this->commandes;
    }

    public function addCommande($commande)
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes[] = $commande;
            $commande->addProduit($this);
        }

        return $this;
    }

    public function removeCommande(Commande $commande)
    {
        if ($this->commandes->contains($commande)) {
            $this->commandes->removeElement($commande);
            $commande->removeProduit($this);
        }

        return $this;
    }

    public function __toString(){
        return $this->nom;
    }
}
