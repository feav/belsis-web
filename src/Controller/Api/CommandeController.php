<?php

namespace App\Controller\Api;

use App\Entity\CommandeProduit;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\TableRepository;
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
    private $tableRepository;
    private $produitRepository;
    private $doctrine;

    public function __construct(CommandeRepository $commandeRepository, CommandeProduitRepository $commandeProduitRepository, ProduitRepository $produitRepository, TableRepository $tableRepository)
    {
        $this->commandeRepository = $commandeRepository;
        $this->commandeProduitRepository = $commandeProduitRepository;
        $this->produitRepository = $produitRepository;
        $this->tableRepository = $tableRepository;
    }

    /**
     *Get Commandes id.
     * @Rest\Post("/add", name="new_one")
     *
     * @return Response
     */
    public function newCommande(Request $request)
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
        if($request->get('order_id')){
            $commande = $this->commandeRepository->find($request->get('order_id'));
            $produit = $this->produitRepository->find($request->get('product_id'));
            $commandeProduit = $this->isProductInCmd($request->get('product_id'), $request->get('order_id'));
            if(is_null($commandeProduit))
                $commandeProduit =  new CommandeProduit();

            $commandeProduit->setProduit($produit);
            $commandeProduit->setCommande($commande);
            $commandeProduit->setQuantite($request->get('qty'));
            $commandeProduit->setPrix( ($request->get('qty')*$produit->getPrix()) );

            $commande->setMontant( ($commande->getMontant() + $commandeProduit->getPrix()) );
            $entityManager->persist($commandeProduit);
        }
        else{
            $produit = $this->produitRepository->find($request->get('product_id'));
            $commandeProduit = $this->isProductInCmd($request->get('product_id'), $request->get('order_id'));
                if(is_null($commandeProduit))
                    $commandeProduit =  new CommandeProduit();

            $commande = new Commande();
            $commande->setEtat("edition"); 
            $commande->setDate( new \Datetime() ); 
            $commande->setUser($user); 
            $commande->setTable($this->tableRepository->find($request->get('table_id')));
            $commande->setUser($user);

            $commandeProduit->setProduit($produit);
            $commandeProduit->setCommande($commande);
            $commandeProduit->setQuantite($request->get('qty'));
            $commandeProduit->setPrix( ($request->get('qty')*$produit->getPrix()) );

            $commande->setMontant( ($commande->getMontant() + $commandeProduit->getPrix()) );
            $entityManager->persist($commande);
            $entityManager->persist($commandeProduit);
        }

        $entityManager->flush();

        return $this->handleView($this->view(
            [
                'status' => 'success',
                'message' => "Commande ajoutée"
            ], 
            Response::HTTP_OK)
        );
    }

    /**
     *Get Commandes id.
     * @Rest\Post("/add-many", name="new")
     *
     * @return Response
     */
    public function newManyCommande(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $data = json_decode($request->getContent(), true);
        $entityManager = $this->getDoctrine()->getManager();
        $productsCmd = $data['products_cmd'];   
        $priceAdd = 0;
        if($data['order_id']){
            $commande = $this->commandeRepository->find($data['order_id']);
            foreach ($productsCmd as $key => $value) {

                $produit = $this->produitRepository->find($value['id']);
                $commandeProduit = $this->isProductInCmd($value['id'], $data['order_id']);
                if(is_null($commandeProduit))
                    $commandeProduit =  new CommandeProduit();

                $commandeProduit->setProduit($produit);
                $commandeProduit->setCommande($commande);
                $commandeProduit->setQuantite($value['qty']);
                $commandeProduit->setPrix( ($value['qty']*$produit->getPrix()) );
                $entityManager->persist($commandeProduit);                
                $priceAdd += $commandeProduit->getPrix();
            }
            $commande->setMontant( $commande->getMontant() + $priceAdd );
        }
        else{
            $commande = new Commande();
            $commande->setEtat("edition"); 
            $commande->setDate( new \Datetime() ); 
            $commande->setUser($user); 
            $commande->setTable($this->tableRepository->find($request->get('table_id')));
            $commande->setUser($user);
            $entityManager->persist($commande);

            foreach ($productsCmd as $key => $value) {
                $produit = $this->produitRepository->find($value['id']);
                $commandeProduit = $this->isProductInCmd($value['id'], $data['order_id']);
                if(is_null($commandeProduit))
                    $commandeProduit =  new CommandeProduit();

                $commandeProduit->setProduit($produit);
                $commandeProduit->setCommande($commande);
                $commandeProduit->setQuantite($value['qty']);
                $commandeProduit->setPrix( ($value['qty']*$produit->getPrix()) );
                $entityManager->persist($commandeProduit);                
                $priceAdd += $commandeProduit->getPrix();
            }
            $commande->setMontant( $commande->getMontant() + $priceAdd );
            $entityManager->persist($commande);
        }

        $entityManager->flush();

        return $this->handleView($this->view(
            $commande->getId(), 
            Response::HTTP_OK)
        );
    }

    public function isProductInCmd($produit_id, $commande_id){
        $commandeProduit = $this->commandeProduitRepository ->findOneBy(['produit'=>$produit_id, 'commande'=>$commande_id]);

        return $commandeProduit;
    }

    /**
     *Get Commandes id.
     * @Rest\Get("/delete", name="delete_order")
     *
     * @return Response
     */
    public function deleteCommande(Request $request)
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
     * @Rest\Get("/remove-product", name="remove_product_commande")
     *
     * @return Response
     */
    public function removeProduct(Request $request)
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
     * @Rest\Get("/change-etat", name="change_etat")
     *
     * @return Response
     */
    public function changeEtat(Request $request)
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
        $commande = $this->commandeRepository->find($request->get('order_id'));
        $commande->setEtat($request->get('etat'));

        $entityManager->flush();

        return $this->handleView($this->view(
            [
                'status' => 'success',
                'message' => "Etat modifié"
            ], 
            Response::HTTP_OK)
        );
    }

    /**
     *Remove product to commande.
     * @Rest\Get("/get-product-by-order", name="get_product_by_order")
     *
     * @return Response
     */
    public function getProductByOrder(Request $request)
    {
        $user = $this->authToken($request);
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
                'id'=> $value->getProduit()->getId(),
                'name'=> $value->getProduit()->getNom(),
                'icon'=> $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getProduit()->getImage(),
                'price'=>$value->getProduit()->getPrix(),
                'qty_stock'=>$value->getProduit()->getQuantite(),
            ];
        }

        return $this->handleView($this->view(
            $commandeProduitArray, 
            Response::HTTP_OK)
        );
    }

    /**
     *Get Commandes id
     * @Rest\Get("/get", name="get_commande")
     *
     * @return Response
     */
    public function getCommandeById(Request $request)
    {
        $user = $this->authToken($request);
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
                'id'=>$value->getProduit()->getId(),
                'detail_id'=>$value->getId(),
                'name'=> $value->getProduit()->getNom(),
                'icon'=> $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getProduit()->getImage(),
                'qty'=>$value->getQuantite(),
                'qty_stock'=>$value->getProduit()->getQuantite(),
                'price'=>$value->getPrix(),
            ];
            $totalProduit += $value->getQuantite();
            $totalPrice += $value->getPrix();
        }

        return $this->handleView($this->view(
            [
                'id'=> $commande->getId(),
                'date_create'=> $commande->getDate()->format('Y-m-d H:i:s'),
                'etat'=> $commande->getEtat(),
                'qty'=> $totalProduit,
                'price'=> $totalPrice,
                'table'=> is_null($commande->getTable()) ? "" : $commande->getTable()->getNom(),
                'detail'=> $commandeProduitArray
            ], 
            Response::HTTP_OK)
        );
    }

    /**
     *Remove product to commande.
     * @Rest\Get("/get-by-table", name="get_by_table")
     *
     * @return Response
     */
    public function getByTable(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $commandes = $this->commandeRepository->findBy(['table'=>$request->get('table_id')]);
        $commandesArray = [];
        foreach ($commandes as $key => $value) {
            $commandesArray[] = [
                'id'=> $value->getId(),
                'date_create'=> $value->getDate()->format('Y-m-d H:i:s'),
                'etat'=> $value->getEtat(),
                'name'=> $value->getCode(),
                'table'=> $value->getTable()->getNom(),
                'qty'=> $this->getDetailNbrCmd($value)['totalProduit'],
                'price'=> $this->getDetailNbrCmd($value)['totalPrice'],
            ];
        }

        return $this->handleView($this->view(
            $commandesArray,
            Response::HTTP_OK)
        );
    }

    public function getDetailNbrCmd($commande){

        $totalProduit = $totalPrice =0;
        foreach ($commande->getCommandeProduit() as $key => $value) {
            $totalProduit += $value->getQuantite();
            $totalPrice += $value->getPrix();
        }
        return ['totalProduit'=> $totalProduit, 'totalPrice'=> $totalPrice ] ;
    }

    /**
     *Remove product to commande.
     * @Rest\Get("/get-by-user", name="get_by_user")
     *
     * @return Response
     */
    public function getByUser(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $commandes = $this->commandeRepository->findBy(['user'=>$user->getId()]);
        $commandesArray = [];
        foreach ($commandes as $key => $value) {
            $commandesArray[] = [
                'id'=> $value->getId(),
                'date_create'=> $value->getDate()->format('Y-m-d H:i:s'),
                'etat'=> $value->getEtat(),
                'name'=> $value->getCode(),
                'table'=> $value->getTable()->getNom(),
                'qty'=> $this->getDetailNbrCmd($value)['totalProduit'],
                'price'=> $this->getDetailNbrCmd($value)['totalPrice'],
            ];
        }

        return $this->handleView($this->view(
            $commandesArray,
            Response::HTTP_OK)
        );
    }

    /**
     *Remove product to commande.
     * @Rest\Get("/get-by-shop", name="get_by_shop")
     *
     * @return Response
     */
    public function getByShop(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $commandes = $this->commandeRepository->getCommandeAllByRestaurant($user->getRestaurant()->getId());
        $commandesArray = [];
        foreach ($commandes as $key => $value) {
            $commandeItem = $this->commandeRepository->find($value['id']);
            $commandesArray[] = [
                'id'=> $value['id'],
                'date'=> $value['date'],
                'etat'=> $value['etat'],
                'name'=> $value['code'],
                'table'=> is_null($commandeItem->getTable()) ? "" : $commandeItem->getTable()->getNom(),
                'qty'=> $this->getDetailNbrCmd($commandeItem)['totalProduit'],
                'price'=> $this->getDetailNbrCmd($commandeItem)['totalPrice'],
            ];
        }

        return $this->handleView($this->view(
            $commandesArray,
            Response::HTTP_OK)
        );
    }
}
