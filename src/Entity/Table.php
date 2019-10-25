<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TableRepository")
 * @ORM\Table(name="_table")
 */
class Table
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
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="float")
     */
    private $coord_x;

    /**
     * @ORM\Column(type="float")
     */
    private $coord_y;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Restaurant", inversedBy="tables")
     * @ORM\JoinColumn(nullable=false)
     */
    private $restaurant;

    public function getId(): ?int
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

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getCoordX()
    {
        return $this->coord_x;
    }

    public function setCoordX($coord_x)
    {
        $this->coord_x = $coord_x;

        return $this;
    }

    public function getCoordY()
    {
        return $this->coord_y;
    }

    public function setCoordY($coord_y)
    {
        $this->coord_y = $coord_y;

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

    public function __toString(){
        return $this->nom;
    }
}
