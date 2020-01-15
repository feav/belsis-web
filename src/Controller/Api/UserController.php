<?php

namespace App\Controller\Api;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\CommandeRepository;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\UserBundle\Model\UserManagerInterface;

use App\Entity\Commande;
use App\Entity\User;
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
    
    public function __construct(UserRepository $userRepository, EncoderFactoryInterface $encoderFactory, CommandeRepository $commandeRepository){
      $this->userRepository = $userRepository;
      $this->encoderFactory = $encoderFactory;
      $this->commandeRepository = $commandeRepository;
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

    public function getUserEssential(User $user){
      return [
              'id' => $user->getId(),
              'username' => $user->getUserName(),
              'email' => $user->getEmail(),
              'nom' => $user->getNom(),
              'prenom' => $user->getPrenom()
              'role' => $user->getRole()
          ];
    }

}
