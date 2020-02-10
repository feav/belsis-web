<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GlobalService;
use App\Repository\DiscussionRepository;
use App\Repository\MessageRepository;

class MessagerieController extends AbstractController
{
    private $discussionRepository;
    private $messageRepository;
    private $global_s;
    
    public function __construct(GlobalService $global_s, DiscussionRepository $discussionRepository, MessageRepository $messageRepository){
      $this->discussionRepository = $discussionRepository;
      $this->messageRepository = $messageRepository;
      $this->global_s = $global_s;
    }

    /**
     * @Route("/dashboard/messagerie/{id_discussion}", name="messagerie")
     */
    public function index($id_discussion)
    {
        $discussion = $this->discussionRepository->find($id_discussion);
        return $this->render('messagerie/index.html.twig', [
		    'ws_url' => 'localhost:8080',
        'discussion'=>$discussion,
		    'messages'=>$discussion->getMessages(),
		    'user'=>$this->getUser()
		]);
    }
}
