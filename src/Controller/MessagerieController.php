<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GlobalService;
use App\Entity\Discussion;
use App\Repository\DiscussionRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;

class MessagerieController extends AbstractController
{
    private $discussionRepository;
    private $messageRepository;
    private $userRepository;
    private $global_s;
    
    public function __construct(GlobalService $global_s, DiscussionRepository $discussionRepository, MessageRepository $messageRepository, UserRepository $userRepository){
      $this->discussionRepository = $discussionRepository;
      $this->messageRepository = $messageRepository;
      $this->userRepository = $userRepository;
      $this->global_s = $global_s;
    }

    /**
     * @Route("/dashboard/discussion/new/{id_user}", name="discussion_new")
     */
    public function discussionNew($id_user = null)
    {   
        $entityManager = $this->getDoctrine()->getManager();
        if(!is_null($id_user)){
          $discussion = new Discussion();
          $discussion->setNom("#");
          $discussion->addUser($this->getUser());

          $entityManager->persist($discussion);
          $entityManager->flush();

          return $this->redirectToRoute('messagerie',['id_discussion'=>$discussion->getId()]);
        } 
        else{
          //broad cast
          return new Response('Message en diffusion');
        }
    }

    /**
     * @Route("/dashboard/messagerie/{id_discussion}", name="messagerie")
     */
    public function chat($id_discussion)
    {
        $discussion = $this->discussionRepository->find($id_discussion);
        return $this->render('messagerie/chat.html.twig', [
  		    'ws_url' => 'localhost:8080',
          'discussion'=>$discussion,
  		    'user'=>$this->getUser()
  		]);
    }
    
    /**
     * @Route("/dashboard/discussions", name="discussions")
     */
    public function discussions()
    {
        $discussions = $this->getUser()->getDiscussions();
        return $this->render('messagerie/discussion.html.twig', [
          'discussions'=>$discussions,
      ]);
    }
}
