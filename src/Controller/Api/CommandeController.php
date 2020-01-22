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

use App\Service\ProduitService;
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
    private $produit_s;

    public function __construct(CommandeRepository $commandeRepository, CommandeProduitRepository $commandeProduitRepository, ProduitRepository $produitRepository, TableRepository $tableRepository, ProduitService $produit_s)
    {
        $this->commandeRepository = $commandeRepository;
        $this->commandeProduitRepository = $commandeProduitRepository;
        $this->produitRepository = $produitRepository;
        $this->tableRepository = $tableRepository;
        $this->produit_s = $produit_s;
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
            else{
                $commande->setMontant($commande->getMontant() - $commandeProduit->getPrix() );
                if($commande->getEtat() == "en_cours"){
                    $this->produit_s->changeStock("add", $commandeProduit->getQuantite(), $request->get('product_id'));
                }
            }

            $commandeProduit->setProduit($produit);
            $commandeProduit->setCommande($commande);
            $commandeProduit->setQuantite($request->get('qty'));
            $commandeProduit->setPrix( ($request->get('qty')*$produit->getPrix()) );

            $commande->setMontant( ($commande->getMontant() + $commandeProduit->getPrix()) );
            if($commande->getEtat() == "en_cours")
                $this->produit_s->changeStock("remove", $request->get('qty'), $request->get('product_id') );
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
            $commande->setRestaurant($user->getRestaurant()->getId()); 
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
        $qtyAdd = 0;
        if($data['order_id']){
            $commande = $this->commandeRepository->find($data['order_id']);
            foreach ($productsCmd as $key => $value) {
                $produit = $this->produitRepository->find($value['id']);
                $commandeProduit = $this->isProductInCmd($value['id'], $data['order_id']);
                if(is_null($commandeProduit)){
                    $commandeProduit =  new CommandeProduit();
                }
                else{
                    $commande->setMontant($commande->getMontant() - $commandeProduit->getPrix() );
                    if($commande->getEtat() == "en_cours"){
                        $this->produit_s->changeStock("add", $commandeProduit->getQuantite(), $value['id']);
                    }
                }

                $commandeProduit->setProduit($produit);
                $commandeProduit->setCommande($commande);
                $commandeProduit->setQuantite($value['qty']);
                $commandeProduit->setPrix( ($value['qty']*$produit->getPrix()) );
                $entityManager->persist($commandeProduit); 

                $priceAdd += $commandeProduit->getPrix();
                if($commande->getEtat() == "en_cours")
                    $this->produit_s->changeStock("remove", $value['qty'], $value['id']);
            }
            $commande->setMontant( $commande->getMontant() + $priceAdd );
            
        }
        else{
            $commande = new Commande();
            $commande->setEtat("edition"); 
            $commande->setDate( new \Datetime() ); 
            $commande->setUser($user); 
            $commande->setRestaurant($user->getRestaurant()->getId()); 
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

    public function updateStockChangeCmd($commande, $request){
        foreach ($commande->getCommandeProduit() as $key => $value) {
            if( $request->get('etat') == "en_cours"  ){
                $this->produit_s->changeStock("remove", $value->getQuantite(), $value->getProduit()->getId() );
            }
        }
        return 1;
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

        $commande = $this->commandeRepository->find($request->get('order_id'));
        if( $commande->getEtat() == "en_cours"  )
            $this->produit_s->changeStock("add", $commandeProduit->getQuantite(), $request->get('product_id') );
        $commande->setMontant($commande->getMontant() - $commandeProduit->getPrix());

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
        $this->updateStockChangeCmd($commande, $request);
        $commande->setEtat($request->get('etat'));
        if($request->get('etat') == "prete")
            $commande->setCuisinier($user->getId());

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
            if($value->getProduit()->getImage())
                $image = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getProduit()->getImage());
            else
                $image = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/image-default.jpeg");

            $commandeProduitArray[] = [
                'id'=> $value->getProduit()->getId(),
                'name'=> $value->getProduit()->getNom(),
                'icon'=> $image,
                'price'=>$value->getProduit()->getPrix(),
                'qty_stock'=>$value->getProduit()->getQuantite(),
                'is_delete'=>$value->getProduit()->getIsDelete()
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
            if($value->getProduit()->getImage())
                $image = $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getProduit()->getImage();
            else
                $image = $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/image-default.jpeg";

            $commandeProduitArray[] = [
                'id'=>$value->getProduit()->getId(),
                'detail_id'=>$value->getId(),
                'name'=> $value->getProduit()->getNom(),
                'icon'=> $image,
                'qty'=>$value->getQuantite(),
                'qty_stock'=>$value->getProduit()->getQuantite(),
                'is_delete'=>$value->getProduit()->getIsDelete(),
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
                'cuisinier'=> $commande->getCuisinier(),
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
                'cuisinier'=> $value->getCuisinier(),
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
                'table'=> is_null($value->getTable()) ? "" : $value->getTable()->getNom(),
                'qty'=> $this->getDetailNbrCmd($value)['totalProduit'],
                'price'=> $this->getDetailNbrCmd($value)['totalPrice'],
                'cuisinier'=> $value->getCuisinier(),
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

        $commandes = $this->commandeRepository->findBy(['restaurant'=>$user->getRestaurant()->getId()]);
        $commandesArray = [];
        foreach ($commandes as $key => $value) {
            $commandesArray[] = [
                'id'=> $value->getId(),
                'date'=> $value->getDate(),
                'etat'=> $value->getEtat(),
                'name'=> $value->getCode(),
                'table'=> is_null($value->getTable()) ? "" : $value->getTable()->getNom(),
                'qty'=> $this->getDetailNbrCmd($value)['totalProduit'],
                'price'=> $this->getDetailNbrCmd($value)['totalPrice'],
                'cuisinier'=> $value->getCuisinier(),
            ];
        }

        return $this->handleView($this->view(
            $commandesArray,
            Response::HTTP_OK)
        );
    }

    /**
     * @Rest\Get("/get-shop-activity", name="get_shop_activity")
     *
     * @return Response
     */
    public function getByShopActivity(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $commandes = $this->commandeRepository->findBy(['restaurant'=>$user->getRestaurant()->getId()]);
        $activity = $activity[]['edition'] = $activity[]['prete'] = $activity[]['en_cours'] = $activity[]['remove'] = $activity[]['paye'] = [];

        $cmd_edition = $cmd_prete = $cmd_cours = $cmd_delete = $cmd_paye =  $price_cmd_cours = $price_cmd_delete = $price_cmd_paye = $price_cmd_edition = $price_cmd_prete= 0;
        foreach ($commandes as $key => $value) {
            if($value->getEtat() == "edition"){
                $price_cmd_cours += $value->getMontant();
                $activity['edition'] = [
                    'totalCommande'=> ++$cmd_edition,
                    'totalPrice'=> $price_cmd_prete
                ];
            } 
            if($value->getEtat() == "prete"){
                $price_cmd_cours += $value->getMontant();
                $activity['prete'] = [
                    'totalCommande'=> ++$cmd_prete,
                    'totalPrice'=> $price_cmd_cours
                ];
            } 
            if($value->getEtat() == "en_cours"){
                $price_cmd_cours += $value->getMontant();
                $activity['en_cours'] = [
                    'totalCommande'=> ++$cmd_cours,
                    'totalPrice'=> $price_cmd_cours
                ];
            } 
            elseif($value->getEtat() == "remove"){
                $price_cmd_delete += $value->getMontant();
                $activity['remove'] = [
                    'totalCommande'=> ++$cmd_delete,
                    'totalPrice'=> $price_cmd_delete
                ];
            } 
            elseif($value->getEtat() == "paye"){
                $price_cmd_paye += $value->getMontant();
                $activity['paye'] = [
                    'totalCommande'=> ++$cmd_paye,
                    'totalPrice'=> $price_cmd_paye
                ];
            } 
        }

        return $this->handleView($this->view(
            [
                'activity' => $activity,
                'chiffreAffaire' => $price_cmd_paye
            ],
            Response::HTTP_OK)
        );
    }

    /**
     * @Rest\Get("/get-user-activity", name="get_user_activity")
     *
     * @return Response
     */
    public function getByUserActivity(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $commandes = [];
        if($user->getRole() == "serveur")
            $commandes = $this->commandeRepository->findBy(['user'=>$user->getId(), 'restaurant'=>$user->getRestaurant()->getId()]);

        $activity = $activity[]['edition'] = $activity[]['prete'] = $activity[]['en_cours'] = $activity[]['remove'] = $activity[]['paye'] = [];

        $cmd_edition = $cmd_prete = $cmd_cours = $cmd_delete = $cmd_paye =  $price_cmd_cours = $price_cmd_delete = $price_cmd_paye = $price_cmd_edition = $price_cmd_prete= 0;
        foreach ($commandes as $key => $value) {
            if($value->getEtat() == "edition"){
                $price_cmd_cours += $value->getMontant();
                $activity['edition'] = [
                    'totalCommande'=> ++$cmd_edition,
                    'totalPrice'=> $price_cmd_prete
                ];
            } 
            if($value->getEtat() == "prete"){
                $price_cmd_cours += $value->getMontant();
                $activity['prete'] = [
                    'totalCommande'=> ++$cmd_prete,
                    'totalPrice'=> $price_cmd_cours
                ];
            } 
            if($value->getEtat() == "en_cours"){
                $price_cmd_cours += $value->getMontant();
                $activity['en_cours'] = [
                    'totalCommande'=> ++$cmd_cours,
                    'totalPrice'=> $price_cmd_cours
                ];
            } 
            elseif($value->getEtat() == "remove"){
                $price_cmd_delete += $value->getMontant();
                $activity['remove'] = [
                    'totalCommande'=> ++$cmd_delete,
                    'totalPrice'=> $price_cmd_delete
                ];
            } 
            elseif($value->getEtat() == "paye"){
                $price_cmd_paye += $value->getMontant();
                $activity['paye'] = [
                    'totalCommande'=> ++$cmd_paye,
                    'totalPrice'=> $price_cmd_paye
                ];
            } 
        }

        return $this->handleView($this->view(
            [
                'activity' => $activity,
                'chiffreAffaire' => $price_cmd_paye
            ],
            Response::HTTP_OK)
        );
    }
}
