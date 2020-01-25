<?php

namespace App\Controller\Api;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGenerator;
use App\Repository\CommandeRepository;
use App\Repository\CommandeProduitRepository;
use App\Repository\RestaurantRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\HttpFoundation\RequestStack;
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
        $userFind = $this->userRepository->find($request->get('user_id'));
        $infos = $this->getUserEssential($userFind);

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
      if($user->getRole() == "cuisinier"){
        //$commandes = $this->commandeRepository->getByCuisinier($user->getId());
        $commandes = $this->commandeRepository->findBy(['cuisinier'=>$user->getRestaurant()->getId()]);
      }
      elseif($user->getRole() == "serveur"){
        //$commandes = $this->commandeRepository->getByServeur($user->getId());
        $commandes = $this->commandeRepository->findBy(['user'=>$user->getId()]);
      }
      
      $totalCommande = $totalPrice =0;
      foreach ($commandes as $key => $value) {
        $totalCommande++;
        $totalPrice += $value->getMontant();
      }

      if($user->getAvatar())
            $avatar = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/dynamiques/profile/".$user->getAvatar());
        else
            $avatar = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/dynamiques/profile/user.png");
      
      $activity = $this->initActivity();
      $cmd_edition = $cmd_prete = $cmd_cours = $cmd_delete = $cmd_paye =  $price_cmd_cours = $price_cmd_delete = $price_cmd_paye = $price_cmd_edition = $price_cmd_prete= 0;


      foreach ($commandes as $key => $value) {
          if($value->getEtat() == "edition"){
              $price_cmd_cours += $value->getMontant();
              $activity['edition'] = [
                  'totalCommande'=> ++$cmd_edition,
                  'totalPrice'=> $price_cmd_prete
              ];
          } 
          if($value->getEtat() == "prete"){
              $price_cmd_cours += $value->getMontant();
              $activity['prete'] = [
                  'totalCommande'=> ++$cmd_prete,
                  'totalPrice'=> $price_cmd_cours
              ];
          } 
          if($value->getEtat() == "en_cours"){
              $price_cmd_cours += $value->getMontant();
              $activity['en_cours'] = [
                  'totalCommande'=> ++$cmd_cours,
                  'totalPrice'=> $price_cmd_cours
              ];
          } 
          elseif($value->getEtat() == "remove"){
              $price_cmd_delete += $value->getMontant();
              $activity['remove'] = [
                  'totalCommande'=> ++$cmd_delete,
                  'totalPrice'=> $price_cmd_delete
              ];
          } 
          elseif($value->getEtat() == "paye"){
              $price_cmd_paye += $value->getMontant();
              $activity['paye'] = [
                  'totalCommande'=> ++$cmd_paye,
                  'totalPrice'=> $price_cmd_paye
              ];
          } 
      }

      return [
              'id' => $user->getId(),
              'username' => $user->getUserName(),
              'email' => $user->getEmail(),
              'nom' => $user->getNom(),
              'prenom' => $user->getPrenom(),
              'role' => $user->getRole(),
              'avatar' => $avatar,
              'totalCommande' => $totalCommande,
              'totalPrice' => $totalPrice,
              'activity' => $activity,
              'chiffreAffaire' => $price_cmd_paye
          ];
    }

    public function initActivity(){

      $activity = $activity[]['edition'] = $activity[]['prete'] = $activity[]['en_cours'] = $activity[]['remove'] = $activity[]['paye'] = [];
      $activity['edition'] = [
          'totalCommande'=> 0,
          'totalPrice'=> 0
      ];
      $activity['prete'] = [
          'totalCommande'=> 0,
          'totalPrice'=> 0
      ];
      $activity['en_cours'] = [
          'totalCommande'=> 0,
          'totalPrice'=> 0
      ];
      $activity['remove'] = [
          'totalCommande'=> 0,
          'totalPrice'=> 0
      ];
      $activity['paye'] = [
          'totalCommande'=> 0,
          'totalPrice'=> 0
      ];

      return $activity;
    }

    /**
     * @Rest\Post("/add", name="add_user")
     *
     * @return Response
     */
    public function addUser(Request $request, UserManagerInterface $userManager)
    {
        $userConnect = $this->authToken($request);
        if (is_array($userConnect)) {
            return $this->handleView(
                $this->view(
                    $userConnect,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $entityManager = $this->getDoctrine()->getManager();

        $user = new User();
        if($request->get('user_id'))
          $user = $this->userRepository->find($request->get('user_id'));

        $existEmail = $userManager->findUserByEmail($request->get('email'));
        if(!is_null($existEmail) && !$request->get('user_id')){
            return $this->handleView(
                $this->view(
                    [
                      "status" => "error",
                      "message"=> "Un utilisateur existe déjà avec cet email."
                    ],
                    Response::HTTP_UNAUTHORIZED)
            );
        }
        $userNameExist = $userManager->findUserByUsername($request->get('username'));
        if(!is_null($userNameExist) && !$request->get('user_id')){
            return $this->handleView(
                $this->view(
                    [
                      "status" => "error",
                      "message"=> "Un utilisateur existe déjà avec ce nom d\'utilisateur."
                    ],
                    Response::HTTP_UNAUTHORIZED)
            );
        }
        $role = $request->get('role');
        if($role){
            $user->setRoles(['ROLE_'.strtolower($role)]); 
            $user->setRole($role);
        }
        if($request->get('password'))
            $user->setPlainPassword($request->get('password'));

        $user->setNom($request->get('nom'));
        $user->setPrenom($request->get('prenom'));
        $user->setUsername($request->get('username'));
        $user->setUsernameCanonical($request->get('username'));
        $user->setEmail($request->get('email'));
        $user->setEmailCanonical($request->get('email'));
        $user->setEnabled(true);
        $user->setRestaurant($this->restaurantRepository->find($userConnect->getRestaurant()->getId()));
        
        if ($request->get('avatar')) {
            $nameImage = "avatar-".Date("Yds").".png";
            $savePath = $request->server->get('DOCUMENT_ROOT')."/images/dynamiques/profile/".$nameImage;

            if(strpos($request->get('avatar'), "data:image/") !== false ){
                $base64_string = $request->get('avatar');
                $data = explode( ',', $base64_string );
                file_put_contents($savePath, base64_decode($data[1]));
            }
            /*else{
                $ch = curl_init($request->get('avatar'));
                $fp = fopen($savePath, 'wb');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_exec($ch);
                curl_close($ch);
                fclose($fp);
            }*/
            $user->setAvatar($nameImage);
        }
        
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->handleView($this->view(
            $user->getId(), 
            Response::HTTP_OK)
        );
    }

    /**
    * Modifier le mot de passe de l'utilisateur
    * @Rest\Post("/reset-password", name="user_reset_password")
    *
    * @return Response
    */
    public function resetPassword(Request $request, EventDispatcherInterface $dispatcher, UserManagerInterface $userManager, RequestStack $requestStack){
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $oldPassword = $request->request->get('oldPassword');
        $newPassword = $request->request->get('newPassword');

        $passwordEncoder = $this->get('security.password_encoder');
        if($passwordEncoder->isPasswordValid($user, $oldPassword)){
            $user->setPlainPassword($newPassword);
            $userManager->updateUser($user);

            $event = new UserEvent($user, $requestStack->getCurrentRequest());
            $dispatcher->dispatch(FOSUserEvents::USER_PASSWORD_CHANGED, $event);

            return $this->handleView($this->view(
                $user->getId(),
                Response::HTTP_OK));
        }

        return $this->handleView($this->view(
            [
            'statut' => 'error',
            'message' => "Le mot de passe actuel n'est pas celui de l'utilisateur"
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR));

    }


    /**
    * @Rest\Post("/update-avatar", name="user_avatar")
    *
    * @return Response
    */
    public function resetAvatar(Request $request){
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }
        $entityManager = $this->getDoctrine()->getManager();
        if ($request->get('avatar')) {
            $nameImage = "avatar-".Date("Yds").".png";
            $savePath = $request->server->get('DOCUMENT_ROOT')."/images/dynamiques/profile/".$nameImage;

            if(strpos($request->get('avatar'), "data:image/") !== false ){
                $base64_string = $request->get('avatar');
                $data = explode( ',', $base64_string );
                file_put_contents($savePath, base64_decode($data[1]));
                $user->setAvatar($nameImage);
                $entityManager->flush();
            }
        }

        return $this->handleView($this->view(
            $user->getId(),
            Response::HTTP_OK));
    }
}
