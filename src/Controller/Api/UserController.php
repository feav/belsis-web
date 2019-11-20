<?php

namespace App\Controller\Api;

use App\Entity\Commande;
use App\Entity\User;
use App\Entity\Restaurant;
use App\Form\RestaurantType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * API Controller.
 * @Route("/api/user", name="api_user_")
 */
class UserController extends APIController
{
    /**
     *Get User profile info.
     * @Rest\Post("/new", name="new")
     *
     * @return Response
     */
    public function newUser(Request $request, UserPasswordEncoderInterface $encoder)
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

        $restaurant = $this->getDoctrine()->getRepository(Restaurant::class)->findOneBy(['id' => $restoId]);
        if (empty($data["email"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ email est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        if (empty($data['nom'])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ nom est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data['prenom'])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ prenom est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data['username'])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ username est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data['email'])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ email est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data['password'])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ password est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data['role'])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ role est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        $userExist = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email' => $data['email'], 'username' => $data['username'],
            'restaurant' => $restoId]);
        if (!empty($userExist)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Cet email ou ce username sont déjà utilisés!'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $encodedPwd = $encoder->encodePassword($user, $data['password']);

        $user = new User();
        $user->setRestaurant($restaurant);
        $user->setRoles(array($data['role']));
        $user->setEmail($data['email']);
        $user->setUsername($data['username']);
        $user->setPassword($encodedPwd);
        $user->setPrenom($data['prenom']);
        $user->setNom($data['nom']);
        $this->em->persist($user);
        $this->em->flush();

        return $this->handleView($this->view([
            'status' => 'success',
            'message' => 'Utilisateur crée avec succes.'
        ], 200));
    }

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/get-all", name="get_all")
     *
     * @return Response
     */
    public function getUsers(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->authToken($data['token']);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }

        $restaurant = $user->getRestaurant();
        if (empty($restaurant)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Cet utilisateur n\est dans aucun restaurant.'
                ],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ));
        }
        $restoId = $restaurant->getId();
        $users = $restaurant->getUsers();
        $res = [];
        foreach ($users as $k => $user) {

            $tabUsers = $this->getUserInfos($user);

            $res[] = $tabUsers;
        }
        return $this->handleView($this->view($res, 200));
    }

    public function getUserInfos(User $user)
    {
        return [
            "id" => $user->getId(),
            "nom" => $user->getNom(),
            "username" => $user->getUsername(),
            "email" => $user->getEmail(),
            "role" => $user->getRoles()
        ];
    }
}
