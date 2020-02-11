<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GlobalService;
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
        $discussions = $this->userRepository->getDiscusions();
        return $this->render('messagerie/discussion.html.twig', [
          'discussions'=>$discussions,
      ]);
    }

}
