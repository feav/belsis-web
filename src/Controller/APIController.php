<?php

namespace App\Controller;

use App\Entity\AccessToken;
use App\Entity\Restaurant;
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
                'statut' => 'error',
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

        if (empty($data["token"]) || empty($data["restaurant_id"])) {
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

        $restoId = $data['restaurant_id'];

        if (empty($restoId)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Le champ restaurant_id est vide'
                ],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ));
        }

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
        foreach($produits as $k=>$produit){
            $p['id'] = $produit->getId();
            $p['nom'] = $produit->getNom();
            $p['categorie'] = $produit->getCategorie()->getNom();

            $res[] = $p;
        }
        return $this->handleView($this->view($res, 200));
    }

    /**
     *Get Commands by TablleID'.
     * @Rest\Post("/get-comaandes-by-table-id", name="get_commandes_by_table_id")
     *
     * @return Response
     */
    public function getCommandesByProduitId(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        if (empty($data["token"])) {
            return $this->handleView($this->view($data));
        }
        $this->handleAuth($data['token']);

        if (empty($restoId)) {
            return $this->handleView(
                $this->view(
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }

        $restaurant = $this->getDoctrine()->getRepository(Restaurant::class)->find($restoId);

        if (empty($restaurant)) {
            return $this->handleView(
                $this->view(
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }


        return $this->handleView($this->view($restaurant->getProduits(), 200));
    }

}