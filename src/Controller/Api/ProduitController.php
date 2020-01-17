<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use App\Repository\ProduitRepository;
use App\Repository\RestaurantRepository;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Entity\Restaurant;
use App\Entity\Categorie;
use App\Entity\Stock;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use App\Service\ProduitService;
/**
 * API Controller.
 * @Route("/api/produit", name="api_produit_")
 */
class ProduitController extends APIController
{
    private $produitRepository;
    private $doctrine;
    private $restaurantRepository;
    private $produit_s;

    public function __construct(ProduitRepository $produitRepository, RestaurantRepository $restaurantRepository, ProduitService $produit_s)
    {
        $this->produitRepository = $produitRepository;
        $this->restaurantRepository = $restaurantRepository;
        $this->produit_s = $produit_s;
    }

    /**
    * Get Commandes id
    * @Rest\Get("/get-by-categorie", name="get_by_categorie")
    *
    * @return Response
    */
    public function getProduitByCategory(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $produits = $this->produitRepository->findBy(['categorie'=>$request->get('cat_id')]);
        $produitsArray = [];
        foreach ($produits as $key => $value) {
            $produit = $this->produitRepository->find($value->getId());
            $produitsArray[] = [
                'id'=>$value->getId(),
                'name'=> $value->getNom(),
                'icon'=> $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getImage(),
                'qty_stock'=>$value->getQuantite(),
                'qty'=>0,
                'price'=>$value->getPrix(),
            ];
        }

        return $this->handleView($this->view(
            $produitsArray, 
            Response::HTTP_OK)
        );
    }


      /**
     *Get User profile info.
     * @Rest\Get("/get-by-restaurant", name="get_by_restaurant")
     *
     * @return Response
     */
    public function getByResto(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }
        $produits = $this->restaurantRepository->find($request->get('restaurant_id'))->getProduits();
        $produitsArray = [];
        foreach ($produits as $key => $value) {
          $produitsArray[] = [
            'id'=> $value->getId(),
            'name'=> $value->getNom(),
            'icon'=> $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getImage(),
            'price'=>$value->getPrix(),
            'qty_stock'=>$value->getQuantite(),
          ];
        }
        return $this->handleView($this->view($produitsArray, Response::HTTP_OK));
    }

      /**
     *Get User profile info.
     * @Rest\Get("/edit-stock", name="edit_stock")
     *
     * @return Response
     */
    public function editStock(Request $request)
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
        $produit = $this->produitRepository->find($request->get('product_id'));
        if($request->get('operation') == "reduction")
            $produit->setQuantite($produit->getQuantite() - $request->get('qty'));
        elseif ($request->get('operation') == "add") 
            $produit->setQuantite($produit->getQuantite() + $request->get('qty'));

        $entityManager->flush();
        return $this->handleView($this->view($produit->getId(), Response::HTTP_OK));
    }
}
