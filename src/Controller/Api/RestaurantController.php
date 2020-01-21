<?php

namespace App\Controller\Api;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\RestaurantRepository;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;

use App\Entity\User;
use App\Entity\Restaurant;
use App\Form\RestaurantType;
use App\Repository\UserRepository;

/**
 * API Controller.
 * @Route("/api/restaurant", name="api_restaurant_")
 */
class RestaurantController extends APIController
{
    private $commandeRepository;
    private $userRepository;
    private $encoderFactory;
    
    public function __construct(UserRepository $userRepository, EncoderFactoryInterface $encoderFactory, RestaurantRepository $restaurantRepository){
      $this->userRepository = $userRepository;
      $this->encoderFactory = $encoderFactory;
      $this->restaurantRepository = $restaurantRepository;
    }

    /**
     * @Rest\Get("/get-by-user", name="get_by_user")
     *
     * @return Response
     */
    public function getRestoByUser(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $restaurant = $user->getRestaurant();
        if($restaurant->getLogo())
          $image = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/uploads/restaurant/".$restaurant->getLogo());
        else
          $image = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/image-default.jpeg");
        $restaurantArray = [
          "id"=> $restaurant->getId(),
          "nom"=> $restaurant->getNom(),
          "adresse"=> $restaurant->getAdresse(),
          "logo"=> $image,
          "devise"=> $restaurant->getDevise(),
          "chiffre_affaire"=> $restaurant->getChiffreAffaire(),
          "status"=> $restaurant->getStatus(),
        ];
        return $this->handleView($this->view($restaurantArray, Response::HTTP_OK));
    }

      /**
     * @Rest\Get("/get-by-id", name="get_by_id")
     *
     * @return Response
     */
    public function getById(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }
        $restaurant = $this->restaurantRepository->find($request->get('restaurant_id'));
        if($restaurant->getLogo())
          $image = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/uploads/restaurant/".$restaurant->getLogo());
        else
          $image = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/image-default.jpeg");

        $restaurantArray = [
          "id"=> $restaurant->getId(),
          "nom"=> $restaurant->getNom(),
          "adresse"=> $restaurant->getAdresse(),
          "logo"=> $image,
          "devise"=> $restaurant->getDevise(),
          "chiffre_affaire"=> $restaurant->getChiffreAffaire(),
          "status"=> $restaurant->getStatus(),
        ];
        return $this->handleView($this->view($restaurantArray, Response::HTTP_OK));
    }

}
