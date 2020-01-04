<?php

namespace App\Controller\Api;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    private $userRepository;
    private $encoderFactory;
    
    public function __construct(UserRepository $userRepository, EncoderFactoryInterface $encoderFactory){
      $this->userRepository = $userRepository;
      $this->encoderFactory = $encoderFactory;
    }

    /**
     *Get User profile info.
     * @Rest\Post("/login", name="login")
     *
     * @return Response
     */
    public function login(Request $request, UserManagerInterface $userManager){
        
        $_username = $request->request->get('username');
        $_password = $request->request->get('password');

        $user = $userManager->findUserByUsername($_username);
        $encoder = $this->get('security.password_encoder');

        if(!$user || !$encoder->isPasswordValid($user, $_password)){
            return $this->handleView($this->view(
                [
                'status' => 'error',
                'message' => "identifiant ou mot de passe invalide"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }
        elseif (!$user->isEnabled()) {
            return $this->handleView($this->view(
                [
                'status' => 'error',
                'message' => "Votre compte n'est pas activé"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }
        else{
            return $this->handleView($this->view(
                [
                    'email'=>$user->getUsername(),
                    'username'=>$user->getUsername(),
                    'prenom'=>$user->getPrenom(),
                    'nom'=>$user->getNom(),
                ],
                Response::HTTP_OK)
            );
        }
    }


    /**
     *Get User profile info.
     * @Rest\Post("/get-infos", name="get")
     *
     * @return Response
     */
    public function getProfile(Request $request)
    {
        $user = $this->authToken($request->get('token'));
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
     *Update User profile info.
     * @Rest\Post("/update", name="update")
     *
     * @return Response
     */
    public function update(Request $request, UserPasswordEncoderInterface $encoder)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $restaurant = $user->getRestaurant();
        if (empty($restaurant)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Cet utilisateur n\'est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $restaurant->getId();

        $userExist = $this->getDoctrine()->getRepository(User::class)->findOneBy(['id' => $user->getId(), 'restaurant' => $restoId]);
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
        if (!empty($data['password'])) {
            $encodedPwd = $encoder->encodePassword($user, $data['password']);
            $user->setPassword($encodedPwd);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->handleView($this->view([
            'status' => 'success',
            'message' => 'Mise à jour reussie'
        ], Response::HTTP_OK));
    }

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
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $restaurant = $user->getRestaurant();
        if (empty($restaurant)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Cet utilisateur n\est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $restaurant->getId();

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

        $qb = $this->getDoctrine()->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.restaurant = :restaurant')
            ->andWhere('u.email = :email or u.username = :username')
            ->setParameter('email', $data['email'])->
            setParameter('username', $data['username'])
            ->setParameter('restaurant', $restoId);
        $userExist = $qb->getQuery()->execute();
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
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $restaurant = $user->getRestaurant();
        if (empty($restaurant)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Cet utilisateur n\est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $restaurant->getId();
        $users = $restaurant->getUsers();
        $res = [];
        foreach ($users as $k => $user) {

            $tabUsers = $this->getUserInfos($user);

            $res[] = $tabUsers;
        }
        return $this->handleView($this->view($res, Response::HTTP_OK));
    }

    protected function getUserInfos(User $user)
    {
        return [
            "id" => $user->getId(),
            "nom" => $user->getNom(),
            "username" => $user->getUsername(),
            "email" => $user->getEmail(),
            "role" => $user->getRoles()
        ];
    }

    public function getUserEssential(User $user){
      return [
              'id' => $user->getId(),
              'username' => $user->getUserName(),
              'email' => $user->getEmail(),
              'nom' => $user->getNom(),
              'prenom' => $user->getPrenom()
          ];
    }

}
