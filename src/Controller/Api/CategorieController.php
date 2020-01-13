<?php

namespace App\Controller\Api;

use App\Entity\Categorie;
use App\Entity\Commande;
use App\Repository\CategorieRepository;
use App\Entity\User;
use App\Entity\Restaurant;
use App\Form\RestaurantType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * API Controller.
 * @Route("/api/categorie", name="api_categorie")
 */
class CategorieController extends APIController
{
    private $categorieRepository;
    private $doctrine;

    public function __construct(CategorieRepository $categorieRepository)
    {
        $this->categorieRepository = $categorieRepository;
    }

    /**
    * Get table by shop
    * @Rest\Get("/get-by-shop", name="get_by_shop")
    *
    * @return Response
    */
    public function getByShopUser(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $categories = $this->categorieRepository->findBy(['restaurant'=>$user->getRestaurant()]);
        $categoriesArray = [];

        foreach ($categories as $key => $value) {
            $categoriesArray[] = [
                'id'=>$value->getId(),
                'nom'=>$value->getNom(),
                'image'=>$value->getImage(),
                'description'=>$value->getDescription(),
            ];
        }
        return $this->handleView($this->view(
            $categoriesArray, 
            Response::HTTP_OK)
        );
    }
}
