<?php

namespace App\Controller\Api;

use App\Entity\CommandeProduit;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use App\Repository\CommandeProduitRepository;
use Symfony\Component\Routing\Generator\UrlGenerator;
use App\Entity\Produit;
use App\Entity\Table;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Commande;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * API Controller.
 * @Route("/api/commande", name="api_order_")
 */
class CommandeController extends APIController
{
    private $commandeRepository;
    private $commandeProduitRepository;
    private $produitRepository;
    private $doctrine;

    public function __construct(CommandeRepository $commandeRepository, CommandeProduitRepository $commandeProduitRepository, ProduitRepository $produitRepository)
    {
        $this->commandeRepository = $commandeRepository;
        $this->commandeProduitRepository = $commandeProduitRepository;
        $this->produitRepository = $produitRepository;
    }

    /**
     *Get Commandes id.
     * @Rest\Post("/delete", name="delete_order")
     *
     * @return Response
     */
    public function deleteCommande(Request $request)
    {
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $entityManager = $this->getDoctrine()->getManager();
        $commande = $this->commandeRepository->find($request->get('order_id'));
        $entityManager->remove($commande);
        $entityManager->flush();

        return $this->handleView($this->view(
            [
                'status' => 'success',
                'message' => "Commande supprimé avec succès"
            ], 
            Response::HTTP_OK)
        );
    }

    /**
     *Remove product to commande.
     * @Rest\Post("/remove-product", name="remove_product_commande")
     *
     * @return Response
     */
    public function removeProduct(Request $request)
    {
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $entityManager = $this->getDoctrine()->getManager();
        $commandeProduit = $this->commandeProduitRepository->findOneBy(['produit'=>$request->get('product_id'), 'commande'=>$request->get('order_id')]);

        $entityManager->remove($commandeProduit);
        $entityManager->flush();

        return $this->handleView($this->view(
            [
                'status' => 'success',
                'message' => "Produit retiré de la commande"
            ], 
            Response::HTTP_OK)
        );
    }

    /**
     *Remove product to commande.
     * @Rest\Post("/get-product-by-order", name="get_product_by_order")
     *
     * @return Response
     */
    public function getProductByOrder(Request $request)
    {
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $commandeProduit = $this->commandeProduitRepository->findBy(['commande'=>$request->get('order_id')]);
        $commandeProduitArray = [];
        foreach ($commandeProduit as $key => $value) {
            $commandeProduitArray[] = [
                'id'=> $value->getId(),
                'name'=> $value->getProduit()->getNom(),
                'icon'=> $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getProduit()->getImage(),
                'price'=>$value->getPrix(),
                'total_price'=>$value->getPrix() * $value->getQuantite(),
                'qty'=>$value->getQuantite()
            ];
        }

        return $this->handleView($this->view(
            $commandeProduitArray, 
            Response::HTTP_OK)
        );
    }

    /**
     *Get Commandes id
     * @Rest\Post("/get", name="get_commande")
     *
     * @return Response
     */
    public function getCommandeById(Request $request)
    {
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $commande = $this->commandeRepository->find($request->get('order_id'));
        $commandeProduit = $this->commandeProduitRepository->findBy(['commande'=>$request->get('order_id')]);

        $commandeProduitArray = [];
        $totalProduit = $totalPrice =0;
        foreach ($commandeProduit as $key => $value) {
            $commandeProduitArray[] = [
                'id'=>$value->getId()
                'name'=> $value->getProduit()->getNom(),
                'icon'=> $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getProduit()->getImage(),
                'qty'=>$value->getQuantite(),
                'price'=>$value->getPrix(),
                'total_price'=>$value->getPrix() * $value->getQuantite(),
            ];
            $totalProduit += $value->getQuantite();
            $totalPrice += $value->getPrix() * $value->getQuantite();
        }

        return $this->handleView($this->view(
            [
                'id'=> $commande->getId(),
                'date_create'=> $commande->getDate()->format('Y-m-d H:i:s'),
                'etat'=> $commande->getEtat(),
                'qty'=> $totalProduit,
                'price'=> $totalPrice,
                'detail'=> $commandeProduitArray
            ], 
            Response::HTTP_OK)
        );
    }

}
