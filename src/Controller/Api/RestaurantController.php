<?php

namespace App\Controller\Api;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\RestaurantRepository;
use App\Repository\CommandeRepository;
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
    
    public function __construct(UserRepository $userRepository, EncoderFactoryInterface $encoderFactory, RestaurantRepository $restaurantRepository, CommandeRepository $commandeRepository){
      $this->userRepository = $userRepository;
      $this->encoderFactory = $encoderFactory;
      $this->restaurantRepository = $restaurantRepository;
      $this->commandeRepository = $commandeRepository;
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

    /**
     * @Rest\Post("/add", name="add_restaurant")
     *
     * @return Response
     */
    public function addRestaurant(Request $request)
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

        $restaurant = new Restaurant();
        if($request->get('restaurant_id'))
            $restaurant = $this->restaurantRepository->find($request->get('restaurant_id'));
        else{
          do{
            $restoToken = rtrim(strtr(base64_encode(random_bytes(32)), '+/', '-_'), '=');
            $restaurantExiste = $this->restaurantRepository->findOneBy(['token'=>$restoToken]);
          }while(!is_null($restaurantExiste));
          $restaurant->setToken($restoToken);
        }
        
        $restaurant->setNom($request->get('nom'));
        $restaurant->setAdresse($request->get('adresse'));
        $restaurant->setDevise($request->get('devise'));
        $restaurant->setChiffreAffaire($request->get('chiffre_affaire'));
        
        if ($request->get('logo')) {
            $nameImage = "logo-".Date("Yds").".png";
            $savePath = $request->server->get('DOCUMENT_ROOT')."/images/uploads/restaurant/".$nameImage;

            if(strpos($request->get('logo'), "data:image/") !== false ){
                $base64_string = $request->get('logo');
                $data = explode( ',', $base64_string );
                file_put_contents($savePath, base64_decode($data[1]));
            }
            $restaurant->setLogo($nameImage);
        }
        
        $entityManager->persist($restaurant);
        $entityManager->flush();

        return $this->handleView($this->view(
            $restaurant->getId(), 
            Response::HTTP_OK)
        );
    }

}
