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
     * @ORM\OneToMany(targetEntity="App\Entity\Categorie", mappedBy="restaurant", cascade={"remove"})
     */
    private $categories;    

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\User", mappedBy="restaurant", cascade={"remove"})
     */
    private $users;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Table", mappedBy="restaurant", cascade={"remove"})
     */
    private $tables;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Stock", mappedBy="restaurant", cascade={"remove"})
     */
    private $stocks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Produit", mappedBy="restaurant", cascade={"remove"})
     */
    private $produits;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Commande", mappedBy="restaurant", cascade={"remove"})
     */
    private $commandes;


    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Abonnement", mappedBy="restaurant", cascade={"remove"})
     */
    private $abonnement;

    /**
     * @ORM\Column(type="integer")
     */
    private $chiffreAffaire;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $is_delete;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $token;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->tables = new ArrayCollection();
        $this->stocks = new ArrayCollection();
        $this->produits = new ArrayCollection();
        $this->commandes = new ArrayCollection();
        $this->status = true;
        $this->is_delete = false;
        $this->users = new ArrayCollection();
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

    public function getChiffreAffaire(): ?int
    {
        return $this->chiffreAffaire;
    }

    public function setChiffreAffaire(int $chiffreAffaire): self
    {
        $this->chiffreAffaire = $chiffreAffaire;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAbonnement(): ?Abonnement
    {
        return $this->abonnement;
    }

    public function setAbonnement(?Abonnement $abonnement): self
    {
        $this->abonnement = $abonnement;

        // set (or unset) the owning side of the relation if necessary
        $newRestaurant = $abonnement === null ? null : $this;
        if ($newRestaurant !== $abonnement->getRestaurant()) {
            $abonnement->setRestaurant($newRestaurant);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->setRestaurant($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            // set the owning side to null (unless already changed)
            if ($user->getRestaurant() === $this) {
                $user->setRestaurant(null);
            }
        }

        return $this;
    }

    public function getIsDelete(): ?bool
    {
        return $this->is_delete;
    }

    public function setIsDelete(?bool $is_delete): self
    {
        $this->is_delete = $is_delete;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }
}
