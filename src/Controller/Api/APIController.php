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
    protected $em;

    /**
     * Contructeur de la classe
     */
    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    public function authToken(string $token=null)
    {

        if (empty($token)) {
            return [
                    'status' => 'error',
                    'message' => 'Le token est vide'
                ];
        }

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

    public function getUserInfos(User $user){
        return [
            "id" => $user->getId(),
            "nom" => $user->getNom(),
            "email" => $user->getEmail(),
            "role" => $user->getRoles()
        ];
    }
    /**
     *Get User profile info.
     * @Rest\Post("/get-profile", name="get_profile")
     *
     * @return Response
     */
    public function getProfile(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
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

        $userExist = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $data['id'], 'restaurant' => $restoId]);
        if (empty($userExist)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Aucun utilisateur n\'existe avec cet identifiant dans ce restaurant'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        $res = $this->getUserInfos($user);

        return $this->handleView($this->view($res, 200));
    }

    /**
     *Get User profile info.
     * @Rest\Post("/update-profile", name="update_profile")
     *
     * @return Response
     */
    public function updateProfile(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
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

        $userExist = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $data['id'], 'restaurant' => $restoId]);
        if (empty($userExist)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Aucun utilisateur n\'existe avec cet identifiant dans ce restaurant'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        if (!empty($data['nom'])) {
           $user->setNom($data['nom']);
        }
        if (!empty($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }
        if (!empty($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (!empty($data['role'])) {
            $user->setRoles(array($data['role']));
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->handleView($this->view([
            'status' => 'success',
            'message' => 'Mise à jour reussie'
        ], 200));
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