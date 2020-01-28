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
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Notification;

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
     * @Rest\Get("/send-message", name="send_message")
     *
     * @return Response
     */
    public function sendMessage(Request $request)
    {

        $server_key = 'AAAAIrodwWw:APA91bGM7RRtiYKR9ahU2T7f9OXggGuFz-t67RTnlMOb3tRuNKqDqNWYeEy680qcS3vq0yyVZkmx-kRycYVF2bLTWaLGdCj-I-nFX_iC8IbeUlxytAGDk0pMVXiawr_l8NkAU0Xkwutc';
        $client = new \GuzzleHttp\Client();
        $client->setApiKey($server_key);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

        $message = new Message();
        $message->setPriority('high');
        $message->addRecipient(new Device('daO-GyvavVc:APA91bGHsOdKC6QDlT9k1WNOMUYfOPhZbGAzhKHhopdhI4TXKOGfDYbnGsHZxgV6hWejIRC-upvDvy_ePCdXT54eGKC0kPhLIS6tH9-sne_8E1dXp-VetSQprfV7L0MxNKCeZWgLYh-J'));
        $message
            ->setNotification(new Notification('some title', 'message to alex'))
            ->setData(['key' => 'value'])
        ;

        $response = $client->send($message);
        /*var_dump($response->getStatusCode());
        var_dump($response->getBody()->getContents());*/

        return $this->handleView($this->view(
            [
                'status' => 'success',
                'message' => $response->getStatusCode()
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

        $server_key = 'AAAAIrodwWw:APA91bGM7RRtiYKR9ahU2T7f9OXggGuFz-t67RTnlMOb3tRuNKqDqNWYeEy680qcS3vq0yyVZkmx-kRycYVF2bLTWaLGdCj-I-nFX_iC8IbeUlxytAGDk0pMVXiawr_l8NkAU0Xkwutc';
        $client = new \GuzzleHttp\Client();
        $client->setApiKey($server_key);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

        $message = new Message();
        $message->setPriority('high');
        $message->addRecipient(new Device('cWK9r0lac6k:APA91bG7XogN5zNsOB1YStz2u3o1dm5KvYM9Ukenjm5hOA-HKDPJVqID4b8Nr8U7VYFtd3PDjwAFzCE-shUHtJMUysG4S2xaNwFH0TzXPTMWJUtAK7ew-Q-EensRckj1aBXXVrPKSwHD'));
        $message
            ->setNotification(new Notification('some title', 'some body'))
            ->setData(['key' => 'value'])
        ;

        $response = $client->send($message);
        /*var_dump($response->getStatusCode());
        var_dump($response->getBody()->getContents());*/



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
                'message' => $response->getStatusCode()
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

        if($request->get('dateStart') && $request->get('dateEnd')){
            $commandes = $this->commandeRepository->getByShopActivityByDate(
                $user->getRestaurant()->getId(), new \Datetime($request->get('dateStart')), 
                new \Datetime($request->get('dateEnd'))
            );
        }
        else{
            $commandes = $this->commandeRepository->findBy(['restaurant'=>$user->getRestaurant()->getId()]);
        }
        $activity = $this->buildActivity($commandes);
        return $this->handleView($this->view(
            [
                'activity' => $activity['activity'],
                'chiffreAffaire' => $activity['chiffreAffaire']
            ],
            Response::HTTP_OK)
        );
    }

    /**
     * @Rest\Get("/get-shop-activity-by-echeance", name="get_shop_activity_by_echeance")
     *
     * @return Response
     */
    public function getByShopActivityByEcheance(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $dateNow = new \Datetime();
        $finalActivity = [];
        if($request->get('echeance') == "semaine"){
            $day = $dateNow->format('D');
            $tabWeek = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $dayRang = array_search($day, $tabWeek);
            array_splice($tabWeek, ($dayRang+1));
            $dateStart = date('Y-m-d',strtotime($dateNow->format('Y-m-d') . "-".$dayRang." days"));
            $finalActivity = $this->buildActivityEcheance($dateStart, $tabWeek, $user);
        }
        elseif($request->get('echeance') == "mois"){
            $day = $dateNow->format('d');
            $tabMois = [];
            for($i = 1; $i <= 31; $i++)
                $tabMois[] = $i;

            $dayRang = array_search($day, $tabMois);
            array_splice($tabMois, ($dayRang+1));
            $dateStart = date('Y-m-d',strtotime($dateNow->format('Y-m-d') . "-".$dayRang." days"));
            $finalActivity = $this->buildActivityEcheance($dateStart, $tabMois, $user);
        }
        elseif($request->get('echeance') == "annee"){
            $month = $dateNow->format('M');
            $tabAnnee = [ '01'=>'Jan', '02'=>'Feb', '03'=>'Mar', '04'=>'Apr', '05'=>'May', '06'=>'Jun', '07'=>'Jul', '08'=>'Aug', '09'=>'Sep', '10'=>'Oct', '11'=>'Nov', '12'=>'Dec'];

            $monthRang = array_search($month, $tabAnnee);
            array_splice($tabAnnee, ($monthRang));     
            $topProd = [];   
            foreach ($tabAnnee as $key => $value) {
                $commandes = $this->commandeRepository->getByShopActivityByDatePaye(
                    $user->getRestaurant()->getId(), 
                    new \Datetime($dateNow->format('Y')."-".$key."-"."01"." 00:00:00"), 
                    new \Datetime($dateNow->format('Y')."-".$key."-".
                        cal_days_in_month(CAL_GREGORIAN, $key, $dateNow->format('Y'))." 23:59:59") );

                $totalCommande = $totalPrice = $totalProduit = 0;
                foreach ($commandes as $ky => $val) {
                    $totalCommande++;
                    $totalPrice += $val->getMontant();
                    foreach ($val->getCommandeProduit() as $ky => $vl) {
                        $totalProduit += $vl->getQuantite();
                        $topProd = $this->getTopVente(
                        $vl->getProduit()->getId(), $vl->getQuantite(), $topProd);
                    }
                }
                $finalActivity[] = [
                    'label'=> $value,
                    'nbr_commande_paye'=> $totalCommande,
                    'somme_commande_paye'=> $totalPrice,
                    'total_produit'=> $totalProduit,
                    'top_vente'=> $this->arsortCustom($topProd)
                ];
            }
        }

        return $this->handleView($this->view(
            $finalActivity,
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
        if($user->getRole() == "serveur"){
            $commandes = $this->commandeRepository->findBy(['user'=>$user->getId(), 'restaurant'=>$user->getRestaurant()->getId()]);
        }
        $activity = $this->buildActivity($commandes);
        return $this->handleView($this->view(
            [
                'activity' => $activity['activity'],
                'chiffreAffaire' => $activity['chiffreAffaire']
            ],
            Response::HTTP_OK)
        );
    }

    public function buildActivity($commandes){
        $activity = $this->initActivity();
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
        return ['activity'=>$activity, 'chiffreAffaire'=>$price_cmd_paye];
    }

    public function buildActivityEcheance($dateStart, $tabEchantillons, $user){

        $finalActivity = [];
        foreach ($tabEchantillons as $value) {
            $commandes = $this->commandeRepository->getByShopActivityByDatePaye(
            $user->getRestaurant()->getId(), new \Datetime($dateStart." 00:00:00"), 
            new \Datetime($dateStart." 23:59:59"));

            $topProd = []; 
            $totalCommande = $totalPrice = $totalProduit = 0;
            foreach ($commandes as $key => $val) {
                $totalCommande++;
                $totalPrice += $val->getMontant();
                foreach ($val->getCommandeProduit() as $key => $vl) {
                    $totalProduit += $vl->getQuantite();
                    $topProd = $this->getTopVente(
                        $vl->getProduit()->getId(), $vl->getQuantite(), $topProd);
                }
            }
            $finalActivity[] = [
                'label'=> $value,
                'nbr_commande_paye'=> $totalCommande,
                'somme_commande_paye'=> $totalPrice,
                'total_produit'=> $totalProduit,
                'top_vente'=> $this->arsortCustom($topProd)
            ];
            $dateStart = date('Y-m-d',strtotime($dateStart . "+1 days"));
        }
        return $finalActivity;
    }

    public function initActivity(){

      $activity = $activity[]['edition'] = $activity[]['prete'] = $activity[]['en_cours'] = $activity[]['remove'] = $activity[]['paye'] = [];
      $activity['edition'] = [
          'totalCommande'=> 0,
          'totalPrice'=> 0
      ];
      $activity['prete'] = [
          'totalCommande'=> 0,
          'totalPrice'=> 0
      ];
      $activity['en_cours'] = [
          'totalCommande'=> 0,
          'totalPrice'=> 0
      ];
      $activity['remove'] = [
          'totalCommande'=> 0,
          'totalPrice'=> 0
      ];
      $activity['paye'] = [
          'totalCommande'=> 0,
          'totalPrice'=> 0
      ];

      return $activity;
    }

    public function getTopVente($key, $qty, $topProd){
        if (array_key_exists($key, $topProd)) {
            $topProd[$key] += $qty;
        }
        else{
            $topProd[$key] = $qty;
        }
        return $topProd;
    }

    public function arsortCustom($tab){
        arsort($tab);
        $tabFinal = [];

        foreach ($tab as $key => $value) {
            $produit = $this->produitRepository->find($key);
            if($produit->getImage())
                $image = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$produit->getImage());
            else
                $image = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/image-default.jpeg");
            $tabFinal[] = [
                'id'=> $key,
                "nom"=>$produit->getNom(),
                "qty_stock"=> $produit->getQuantite(),
                "image"=> $image,
                'qty_vendu'=> $value,
            ];
        }
        return $tabFinal;
    }

}
