<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RestaurantRepository")
 */
class Restaurant
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $devise;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Categorie", mappedBy="restaurant")
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Table", mappedBy="restaurant")
     */
    private $tables;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Stock", mappedBy="restaurant")
     */
    private $stocks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Produit", mappedBy="restaurant")
     */
    private $produits;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Commande", mappedBy="restaurant")
     */
    private $commandes;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->tables = new ArrayCollection();
        $this->stocks = new ArrayCollection();
        $this->produits = new ArrayCollection();
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

    public function getAdresse()
    {
        return $this->adresse;
    }

    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getLogo()
    {
        return $this->logo;
    }

    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    public function getDevise()
    {
        return $this->devise;
    }

    public function setDevise($devise)
    {
        $this->devise = $devise;

        return $this;
    }

    /**
     * @return Collection|Categorie[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    public function addCategory($category)
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setRestaurant($this);
        }

        return $this;
    }

    public function removeCategory($category)
    {
        if ($this->categories->contains($category)) {
            $this->categories->removeElement($category);
            // set the owning side to null (unless already changed)
            if ($category->getRestaurant() === $this) {
                $category->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Table[]
     */
    public function getTables()
    {
        return $this->tables;
    }

    public function addTable($table)
    {
        if (!$this->tables->contains($table)) {
            $this->tables[] = $table;
            $table->setRestaurant($this);
        }

        return $this;
    }

    public function removeTable($table)
    {
        if ($this->tables->contains($table)) {
            $this->tables->removeElement($table);
            // set the owning side to null (unless already changed)
            if ($table->getRestaurant() === $this) {
                $table->setRestaurant(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Stock[]
     */
    public function getStocks()
    {
        return $this->stocks;
    }

    public function addStock($stock)
    {
        if (!$this->stocks->contains($stock)) {
            $this->stocks[] = $stock;
            $stock->setRestaurant($this);
        }

        return $this;
    }

    public function removeStock($stock)
    {
        if ($this->stocks->contains($stock)) {
            $this->stocks->removeElement($stock);
            // set the owning side to null (unless already changed)
            if ($stock->getRestaurant() === $this) {
                $stock->setRestaurant(null);
            }
        }

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
            $produit->setRestaurant($this);
        }

        return $this;
    }

    public function removeProduit($produit)
    {
        if ($this->produits->contains($produit)) {
            $this->produits->removeElement($produit);
            // set the owning side to null (unless already changed)
            if ($produit->getRestaurant() === $this) {
                $produit->setRestaurant(null);
            }
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
            $commande->setRestaurant($this);
        }

        return $this;
    }

    public function removeCommande($commande)
    {
        if ($this->commandes->contains($commande)) {
            $this->commandes->removeElement($commande);
            // set the owning side to null (unless already changed)
            if ($commande->getRestaurant() === $this) {
                $commande->setRestaurant(null);
            }
        }

        return $this;
    }

    public function __toString(){
        return $this->nom;
    }
}
