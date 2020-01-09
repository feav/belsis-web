<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use App\Repository\ProduitRepository;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Entity\Restaurant;
use App\Entity\Categorie;
use App\Entity\Stock;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * API Controller.
 * @Route("/api/produit", name="api_produit_")
 */
class ProduitController extends APIController
{
    private $produitRepository;
    private $doctrine;

    public function __construct(ProduitRepository $produitRepository)
    {
        $this->produitRepository = $produitRepository;
    }

    /**
    * Get Commandes id
    * @Rest\Post("/get-by-categorie", name="get_by_categorie")
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
            $produitsArray[] = [
                'id'=>$value->getId(),
                'name'=> $value->getNom(),
                'icon'=> $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getImage(),
                'qty_stock'=>$value->getQuantite(),
                'price'=>$value->getPrix(),
            ];
        }

        return $this->handleView($this->view(
            $produitsArray, 
            Response::HTTP_OK)
        );
    }

}
