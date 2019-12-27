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
     * @ORM\Column(type="text", nullable=true)
     */
    private $image;

    /**
     * @ORM\Column(type="integer")
     */
    private $prix;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="produits")
     */
    private $restaurant;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Commande")
     */
    private $commandes;

    /**
     * @ORM\OneToMany(targetEntity="CommandeProduit", mappedBy="produit")
     */
    private $commandeProduit;

    public function __construct()
    {
        $this->stock = new ArrayCollection();
        $this->commandeProduit = new ArrayCollection();
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

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function setPrix($prix)
    {
        $this->prix = $prix;

        return $this;
    }

    /** Retourne directement la quantité de produit à prélever **/
    public function getQuantite(){
        $stock = $this->getStock();
        //on suppose qu'il y'a un seul stock pour l'instant
        if(!empty($stock)){
            return $stock[0]->getQuantite();
        }else{
            return 0;
        }

    }

    /** Retourne directement la quantité de produit à prélever **/
    public function setQuantite($quantite){
        $stock = $this->getStock();
        //on suppose qu'il y'a un seul stock pour l'instant
        if(!empty($stock)){
            $stock[0]->setQuantite($quantite);
            return $this;
        }else{
            return 0;
        }

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
     * @return Collection|CommandeProduit[]
     */
    public function getCommandeProduit()
    {
        return $this->commandeProduit;
    }


    public function __toString(){
        return $this->nom;
    }

    /**
     * @return Collection|Commande[]
     */
    public function getCommandes(): Collection
    {
        return $this->commandes;
    }

    public function addCommande(Commande $commande): self
    {
        if (!$this->commandes->contains($commande)) {
            $this->commandes[] = $commande;
        }

        return $this;
    }

    public function removeCommande(Commande $commande): self
    {
        if ($this->commandes->contains($commande)) {
            $this->commandes->removeElement($commande);
        }

        return $this;
    }

    public function addCommandeProduit(CommandeProduit $commandeProduit): self
    {
        if (!$this->commandeProduit->contains($commandeProduit)) {
            $this->commandeProduit[] = $commandeProduit;
            $commandeProduit->setProduit($this);
        }

        return $this;
    }

    public function removeCommandeProduit(CommandeProduit $commandeProduit): self
    {
        if ($this->commandeProduit->contains($commandeProduit)) {
            $this->commandeProduit->removeElement($commandeProduit);
            // set the owning side to null (unless already changed)
            if ($commandeProduit->getProduit() === $this) {
                $commandeProduit->setProduit(null);
            }
        }

        return $this;
    }
}
