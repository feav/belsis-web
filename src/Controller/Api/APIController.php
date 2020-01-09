<?php

namespace App\Controller\Api;

use App\Entity\AccessToken;
use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\Restaurant;
use App\Entity\Stock;
use App\Entity\Table;
use App\Entity\User;
use App\Form\RestaurantType;
use App\Repository\RestaurantRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Util\Debug;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\OAuthServerBundle\Model\ClientManagerInterface;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * API Controller.
 * @Route("/api", name="api_")
 */
class APIController extends FOSRestController
{
    private $client_manager;

    /**
     * Contructeur de la classe
     */
    public function __construct(ClientManagerInterface $client_manager)
    {
    }

    public function authToken(Request $request)
    {
        $token = explode(" ", $request->headers->get('Authorization'))[1];
        $em = $this->getDoctrine()->getManager();
        if (empty($token)) {
            return [
                    'status' => 'error',
                    'message' => 'Le token est vide'
                ];
        }

        $accessToken = $em->getRepository(AccessToken::class)->findOneByToken($token);
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
                'status' => 'error',
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
            return $this->handleView($this->view(['status' => 'success'], Response::HTTP_OK));
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
}