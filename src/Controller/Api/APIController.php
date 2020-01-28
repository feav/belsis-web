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

    /**
     * @Rest\Get("/send-devise-token", name="send_devise_token")
     *
     * @return Response
     */
    public function sendDeviceToken(Request $request)
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
        $user->setDeviceToken($request->get('devise-token'));
        $entityManager->persist($user);
        $entityManager->flush();
        return $this->handleView($this->view($user->getId(), Response::HTTP_OK));
    }

    /** 
     * Get header Authorization
     * */
    public function getAuthorizationHeader(){
            $headers = null;
            if (isset($_SERVER['Authorization'])) {
                $headers = trim($_SERVER["Authorization"]);
            }
            else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
                $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
            } elseif (function_exists('apache_request_headers')) {
                $requestHeaders = apache_request_headers();
                // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
                //print_r($requestHeaders);
                if (isset($requestHeaders['Authorization'])) {
                    $headers = trim($requestHeaders['Authorization']);
                }
            }
            return $headers;
        }
    /**
     * get access token from header
     * */
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public function authToken(Request $request)
    {
        $token = $this->getBearerToken();
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