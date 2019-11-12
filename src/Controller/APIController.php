<?php

namespace App\Controller;

use App\Entity\AccessToken;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\Restaurant;
use App\Entity\Stock;
use App\Entity\Table;
use App\Form\RestaurantType;
use App\Repository\RestaurantRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Util\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * API Controller.
 * @Route("/api", name="api_")
 */
class APIController extends FOSRestController
{
    /**
     * *@var ObjectManager
     */
    private $em;

    /**
     * Contructeur de la classe
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function authToken(string $token)
    {
        $accessToken = $this->em->getRepository(AccessToken::class)->findOneByToken($token);
        if (!$accessToken) {
            return [
                'status' => 'error',
                'message' => 'Le token n\'existe pas'
            ];
        }

        if (!$accessToken->getUser()) {
            return [
                'status' => 'error',
                'message' => 'L\'utilisateur n\'existe pas ou plus'
            ];
        }

        if (!$accessToken->getUser()->isEnabled()) {
            return [
                'statut' => 'error',
                'message' => 'Votre compte n\'est pas actif'
            ];
        }

        return $accessToken->getUser();

    }


    /**
     *ListsallResto.
     * @Rest\Post("/new-restaurant", name="new_restaurant")
     *
     * @return Response
     */
    public function newRestaurant(Request $request)
    {
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($restaurant);
            $em->flush();
            return $this->handleView($this->view(['status' => 'ok'], Response::HTTP_CREATED));
        }
        return $this->handleView($this->view($form->getErrors()));
    }

    /**
     *Create Resto.
     * @Rest\Post("/get-restaurants", name="get_all_restaurant")
     *
     * @return Response
     */
    public function getAllRestaurant(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data["token"])) {
            return $this->handleView($this->view($data));
        }
        $this->handleAuth($data['token']);
        $repository = $this->getDoctrine()->getRepository(Restaurant::class);
        $restaurants = $repository->findall();

        return $this->handleView($this->view($restaurants, 200));
    }

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/get-produits", name="get_produits_by_resto_id")
     *
     * @return Response
     */
    public function getProduits(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data["token"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le token est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data["restaurant_id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ restaurant_id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        $user = $this->authToken($data['token']);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }

        $restoId = $data['restaurant_id'];

        $restaurant = $this->getDoctrine()->getRepository(Restaurant::class)->find($restoId);
        if (empty($restaurant)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Ce restaurant n\'existe pas'
                ],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ));
        }
        $produits = $restaurant->getProduits();
        $res = [];
        foreach ($produits as $k => $produit) {
            $tabProduits['id'] = $produit->getId();
            $tabProduits['nom'] = $produit->getNom();
            $tabProduits['image'] = "image.jpg";
            $tabProduits['categorie'] = $produit->getCategorie()->getNom();

            $stock = $produit->getStock();

            //on suppose qu'il y'a un seul stock pour l'instant
            $tabProduits['stock'] = $produit->getQuantite();

            $res[] = $tabProduits;
        }
        return $this->handleView($this->view($res, 200));
    }

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/new-produit", name="new_produit")
     *
     * @return Response
     */
    public function newProduit(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data["token"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le token est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $user = $this->authToken($data['token']);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }
        if (empty($data["restaurant_id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ restaurant_id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data["nom"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ nom est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data["prix"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ prix est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data["quantite"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ quantite est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data["categorie_id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ categorie_id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data["image"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ image est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        $restoId = $data['restaurant_id'];

        $restaurant = $this->getDoctrine()->getRepository(Restaurant::class)->find($restoId);
        if (empty($restaurant)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Ce restaurant n\'existe pas'
                ],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ));
        }

        $categorie = $this->getDoctrine()->getRepository(Categorie::class)->find($data['categorie_id']);

        $stock = new Stock();
        $stock->setRestaurant($restaurant);
        $stock->setNom($data['nom']);
        $stock->setQuantite($data['quantite']);
        $this->em->persist($stock);

        $produit = new Produit();
        $produit->setRestaurant($restaurant);
        $produit->setNom($data['nom']);
        $produit->setPrix($data['prix']);
        $produit->setCategorie($categorie);
        $produit->addStock($stock);

        $this->em->persist($produit);

        $this->em->flush();

        return $this->handleView($this->view(['status' => 'success', 'message' => 'insertion reussie'], 200));
    }

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/update-produit", name="update_produit")
     *
     * @return Response
     */
    public function updateProduit(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data["token"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le token est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $user = $this->authToken($data['token']);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }
        if (empty($data["restaurant_id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ restaurant_id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $data['restaurant_id'];
        $restaurant = $this->getDoctrine()->getRepository(Restaurant::class)->find($restoId);
        if (empty($restaurant)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Ce restaurant n\'existe pas'
                ],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ));
        }

        if (empty($data["id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $produit = $this->getDoctrine()->getRepository(Produit::class)->find($data['id']);
        if (empty($produit)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Ce produit n\'existe pas'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        if (!empty($data["categorie_id"])) {
            $categorie = $this->getDoctrine()->getRepository(Categorie::class)->find($data['categorie_id']);
            $produit->setCategorie($categorie);
        }

        if (!empty($data["quantite"])) {
            $produit->setQuantite($data['quantite']);
        }
        if (!empty($data["nom"])) {
            $produit->setNom($data["nom"]);
        }
        if (!empty($data["prix"])) {
            $produit->setPrix($data["prix"]);
        }

        if (!empty($data["image"])) {
            $produit->setImage($data["image"]);
        }
        $this->em->persist($produit);
        $this->em->flush();

        return $this->handleView($this->view(['status' => 'success', 'message' => 'insertion reussie'], 200));
    }

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/get-produit-detail", name="get_produit_by_resto_id")
     *
     * @return Response
     */
    public function getProduit(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data["token"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le token est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        if (empty($data["restaurant_id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ restaurant_id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $data["restaurant_id"];

        if (empty($data["id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $produitId = $data["id"];

        $user = $this->authToken($data['token']);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }

        $produit = $this->getDoctrine()->getRepository(Produit::class)->findOneBy(['id' => $produitId, 'restaurant' => $restoId]);
        if (empty($produit)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Aucun produit n\'existe avec cet identifiant'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        $res = [];
        $res['id'] = $produit->getId();
        $res['nom'] = $produit->getNom();
        $res['image'] = 'image.jpg';
        $res['prix'] = $produit->getPrix();
        $res['stock'] = $produit->getQuantite();
        $res['categorie'] = $produit->getCategorie()->getNom();


        return $this->handleView($this->view($res, 200));
    }

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/delete-produit", name="delete_produit")
     *
     * @return Response
     */
    public function deleteProduit(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data["token"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le token est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        if (empty($data["restaurant_id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ restaurant_id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $data["restaurant_id"];

        if (empty($data["id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $produitId = $data["id"];

        $user = $this->authToken($data['token']);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }

        $produit = $this->getDoctrine()->getRepository(Produit::class)->findOneBy(['id' => $produitId, 'restaurant' => $restoId]);
        $this->em->remove($produit);
        $this->em->flush();

        return $this->handleView($this->view(['status' => 'success', 'message' => 'suppression reussie'], 200));
    }
    /**
     *Get Commands by TableID'.
     * @Rest\Post("/get-commandes-by-table-id", name="get_commandes_by_table_id")
     *
     * @return Response
     */
    public function getCommandesByTableId(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data["token"])) {
            return $this->handleView($this->view($data));
        }
        $user = $this->authToken($data['token']);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }

        if (empty($data["restaurant_id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ restaurant_id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $data["restaurant_id"];

        if (empty($data["table_id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ table_id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $tableId = $data["table_id"];

        $table = $this->getDoctrine()->getRepository(Table::class)->findOneBy(['id' => $tableId, 'restaurant' => $restoId]);

        if (empty($table)) {
            return $this->handleView(
                $this->view(['status' => 'error', 'message' => 'Cette table n\'existe pas'],
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }

        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findBy(['table' => $tableId]);

        $res = [];
        foreach ($commandes as $k => $cmd) {
            $tab['id'] = $cmd->getId();
            $tab['code'] = $cmd->getCode();
            $produits = $cmd->getProduit();
            foreach ($produits as $r => $produit) {
                $tab['produit'][$r]['id'] = $produit->getId();
                $tab['produit'][$r]['nom'] = $produit->getNom();
                $tab['produit'][$r]['prix'] = $produit->getPrix();
            }

            $res[] = $tab;
        }

        return $this->handleView($this->view($res, 200));
    }

}