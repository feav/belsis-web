<?php

namespace App\Controller\Api;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\CommandeRepository;
use App\Repository\CommandeProduitRepository;
use App\Repository\RestaurantRepository;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\UserBundle\Model\UserManagerInterface;

use App\Entity\Commande;
use App\Entity\User;
use App\Entity\CommandeProduit;
use App\Entity\Restaurant;
use App\Form\RestaurantType;
use App\Repository\UserRepository;

/**
 * API Controller.
 * @Route("/api/user", name="api_user_")
 */
class UserController extends APIController
{
    private $commandeRepository;
    private $userRepository;
    private $encoderFactory;
    private $restaurantRepository;
    private $commandeProduitRepository;
    
    public function __construct(UserRepository $userRepository, EncoderFactoryInterface $encoderFactory, CommandeRepository $commandeRepository, RestaurantRepository $restaurantRepository, CommandeProduitRepository $commandeProduitRepository){
      $this->userRepository = $userRepository;
      $this->encoderFactory = $encoderFactory;
      $this->commandeRepository = $commandeRepository;
      $this->restaurantRepository = $restaurantRepository;
      $this->commandeProduitRepository = $commandeProduitRepository;
    }

    /**
     *Get User profile info.
     * @Rest\Get("/get-infos", name="get")
     *
     * @return Response
     */
    public function getProfile(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $infos = $this->getUserEssential($user);

        return $this->handleView($this->view($infos, Response::HTTP_OK));
    }

      /**
     * @Rest\Get("/get-by-restaurant", name="get_by_restaurant")
     *
     * @return Response
     */
    public function getByResto(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }
        $users = $this->restaurantRepository->find($request->get('restaurant_id'))->getUsers();
        $usersArray = [];
        foreach ($users as $key => $value) {
          $usersArray[] = $this->getUserEssential($value);
        }
        return $this->handleView($this->view($usersArray, Response::HTTP_OK));
    }

      /**
     * @Rest\Get("/get-by-user-restaurant", name="get_by_user_restaurant")
     *
     * @return Response
     */
    public function getByUserResto(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }
        $users = $user->getRestaurant()->getUsers();
        $usersArray = [];
        foreach ($users as $key => $value) {
          $usersArray[] = $this->getUserEssential($value);
        }
        return $this->handleView($this->view($usersArray, Response::HTTP_OK));
    }

    /**
     * @Rest\Get("/get-by-role", name="get_by_role")
     *
     * @return Response
     */
    public function getByRole(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }
        $users = $this->userRepository->findBy(['restaurant'=>$user->getRestaurant()->getId() , 'role'=>$request->get('role')]);
        $usersArray = [];
        foreach ($users as $key => $value) {
          $usersArray[] = $this->getUserEssential($value);
        }
        return $this->handleView($this->view($usersArray, Response::HTTP_OK));
    }

    public function getUserEssential(User $user){

      $commandes = [];
      if($user->getRole() == "cuisinier")
        $commandes = $this->commandeRepository->getByCuisinier($user->getId());
      elseif($user->getRole() == "serveur")
        $commandes = $this->commandeRepository->getByServeur($user->getId());
      
      $totalCommande = $totalPrice =0;
      foreach ($commandes as $key => $value) {
        $totalCommande++;
        $totalPrice += $value['montant'];
      }
      return [
              'id' => $user->getId(),
              'username' => $user->getUserName(),
              'email' => $user->getEmail(),
              'nom' => $user->getNom(),
              'prenom' => $user->getPrenom(),
              'role' => $user->getRole(),
              'avatar' => $user->getAvatar(),
              'totalProduit' => $totalCommande,
              'totalPrice' => $totalPrice
          ];
    }

}
