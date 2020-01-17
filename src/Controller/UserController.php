<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\UserService;
use App\Form\UserType;
use App\Repository\UserRepository;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/dashboad/user")
 */
class UserController extends AbstractController
{
    private $user_s;

    public function __construct(UserService $user_s){
        $this->user_s = $user_s;
    }

    /**
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function index(UserRepository $userRepository): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $users = $userRepository->findAll();
        foreach ($users as $key => $value) {
            if($this->user_s->getSuperRole($value->getRoles()) == "ROLE_SUPER_ADMIN"){
                $value->setRole("super_admin");
            }
            elseif($this->user_s->getSuperRole($value->getRoles()) == "SERVEUR"){
                $value->setRole("serveur");
            }
            elseif($this->user_s->getSuperRole($value->getRoles()) == "ROLE_SERVEUR"){
                $value->setRole("serveur");
            }
            elseif($this->user_s->getSuperRole($value->getRoles()) == "ROLE_ADMIN"){
                $value->setRole("admin");
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
        }

        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="user_new", methods={"GET","POST"})
     */
    public function new(Request $request, UserPasswordEncoderInterface $encoder, UserManagerInterface $userManager): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existEmail = $userManager->findUserByEmail($form->getData()->getEmail());
            if(!is_null($existEmail)){
                $this->addFlash('error', 'Un utilisateur existe déjà avec cet email.');
                return $this->render('user/new.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                    'array_roles'=>['serveur', 'admin', 'superadmin', 'gestionnaire']
                ]);
            }
            $userNameExist = $userManager->findUserByUsername($form->getData()->getUsername());
            if(!is_null($userNameExist)){
                $this->addFlash('error', 'Un utilisateur existe déjà avec ce nom d\'utilisateur.');
                return $this->render('user/new.html.twig', [
                    'user' => $user,
                    'form' => $form->createView(),
                    'array_roles'=>['serveur', 'admin', 'superadmin', 'gestionnaire']
                ]);
            }

            $role = $request->request->get('role');
            if($role){
                $user->setRoles(['ROLE_'.strtolower($role)]); 
                $user->setRole($role);
            }
            if($request->request->get('password'))
                $user->setPlainPassword($request->request->get('password'));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'array_roles'=>['serveur', 'admin', 'superadmin', 'gestionnaire']
        ]);
    }

    /**
     * @Route("/{id}", name="user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="user_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, User $user, UserPasswordEncoderInterface $encoder): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $request->request->get('role');
            if($role){
                $user->setRoles(['ROLE_'.strtolower($role)]); 
                $user->setRole($role);
            }
            if($request->request->get('password'))
                $user->setPlainPassword($request->request->get('password'));
        
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('user_index');
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
            'array_roles'=>['serveur', 'admin', 'superadmin', 'gestionnaire']
        ]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"DELETE"})
     */
    public function delete(Request $request, User $user): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user_index');
    }
}


