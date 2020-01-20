<?php

namespace App\Controller\Api;

use App\Entity\Categorie;
use App\Entity\Commande;
use App\Repository\CategorieRepository;
use App\Repository\RestaurantRepository;
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
    private $restaurantRepository;
    private $doctrine;

    public function __construct(CategorieRepository $categorieRepository, RestaurantRepository $restaurantRepository)
    {
        $this->categorieRepository = $categorieRepository;
        $this->restaurantRepository = $restaurantRepository;
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


    /**
     * @Rest\Post("/add", name="add_categorie")
     *
     * @return Response
     */
    public function addCategorie(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $entityManager = $this->getDoctrine()->getManager();

        $categorie = new Categorie();
        if($request->get('categorie_id'))
            $categorie = $this->categorieRepository->find($request->get('categorie_id'));

        $categorie->setNom($request->get('nom'));
        $categorie->setRestaurant($this->restaurantRepository->find($request->get('restaurant')));
        $categorie->setDescription($request->get('description'));
        
        if ($request->get('image')) {
            $base64_string = $request->get('image');
            $nameImage = Date("Yds").".png";
            $savePath = $request->server->get('DOCUMENT_ROOT')."/images/uploads/categorie/".$nameImage;
            $data = explode( ',', $base64_string );
            file_put_contents($savePath, base64_decode($data[1]));
            $categorie->setImage($nameImage);
        }
        
        $entityManager->persist($categorie);
        $entityManager->flush();

        return $this->handleView($this->view(
            $categorie->getId(), 
            Response::HTTP_OK)
        );
    }

}
