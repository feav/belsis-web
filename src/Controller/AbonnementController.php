<?php

namespace App\Controller;

use App\Entity\Abonnement;
use App\Form\AbonnementType;
use App\Repository\AbonnementRepository;
use App\Repository\RestaurantRepository;
use App\Repository\PlanRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GlobalService;
use Dompdf\Options;
use Dompdf\Dompdf;

use sngrl\PhpFirebaseCloudMessaging\Client as Clt;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Notification;

/**
 * @Route("/dashboad/abonnement")
 */
class AbonnementController extends AbstractController
{

    private $planRepository;
    private $abonnementRepository;
    private $restaurantRepository;
    private $global_s;
    
    public function __construct(AbonnementRepository $abonnementRepository, PlanRepository $planRepository, GlobalService $global_s, RestaurantRepository $restaurantRepository){
      $this->abonnementRepository = $abonnementRepository;
      $this->restaurantRepository = $restaurantRepository;
      $this->planRepository = $planRepository;
      $this->global_s = $global_s;
    }

/**
 * @Route("/send-message-2")
 */
    public function sendMessage(Request $request)
    {

        $server_key = 'AAAAIrodwWw:APA91bGM7RRtiYKR9ahU2T7f9OXggGuFz-t67RTnlMOb3tRuNKqDqNWYeEy680qcS3vq0yyVZkmx-kRycYVF2bLTWaLGdCj-I-nFX_iC8IbeUlxytAGDk0pMVXiawr_l8NkAU0Xkwutc';
        $client = new Clt();
        $client->setApiKey($server_key);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

        $message = new Message();
        $message->setPriority('high');
        $message->addRecipient(new Device('daO-GyvavVc:APA91bGHsOdKC6QDlT9k1WNOMUYfOPhZbGAzhKHhopdhI4TXKOGfDYbnGsHZxgV6hWejIRC-upvDvy_ePCdXT54eGKC0kPhLIS6tH9-sne_8E1dXp-VetSQprfV7L0MxNKCeZWgLYh-J'));
        $message
            ->setNotification(new Notification('some title', 'message to alex'))
            ->setData(['key' => 'value'])
        ;

        $response = $client->send($message);
        /*var_dump($response->getStatusCode());
        var_dump($response->getBody()->getContents());*/

        return $this->handleView($this->view(
            [
                'status' => 'success',
                'message' => $response->getStatusCode()
            ], 
            Response::HTTP_OK)
        );

    }
    /**
     * @Route("/", name="abonnement_index", methods={"GET"})
     */
    public function index(AbonnementRepository $abonnementRepository): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if($this->getUser()->getRole() == "superadmin")
            $abonnements = $abonnementRepository->findAll();
        elseif ($this->getUser()->getRole() == "admin") {
            return $this->redirectToRoute('abonnement_detail_admin', ['id'=>$this->getUser()->getRestaurant()->getId()]);
        }

        $abonnementsArray = [];
        foreach ($abonnements as $key => $value) {
            $abonnementsArray[] =[
                'id'=>$value->getId(),
                'dateCreation'=>$value->getDateCreation()->format('Y-m-d H:i:s'),
                'getDateEcheance'=>$value->getDateEcheance()->format('Y-m-d H:i:s'),
                'echeance'=>[
                    'month'=>(new \DateTime())->diff($value->getDateEcheance())->format('%m'),
                    'day'=>(new \DateTime())->diff($value->getDateEcheance())->format('%d'),
                    'hour'=>(new \DateTime())->diff($value->getDateEcheance())->format('%H'),
                    'minute'=>(new \DateTime())->diff($value->getDateEcheance())->format('%i'),
                    'is_expire'=> (strtotime($value->getDateEcheance()->format('Y-m-d H:i:s')) - time()),
                ],
                'tarif'=>$value->getTarif(),
                'restaurant'=>[
                    'nom'=>$value->getRestaurant() ? $value->getRestaurant()->getNom() : "",
                ],
                'plan'=>$value->getPlan()
            ];
        }
        return $this->render('abonnement/index.html.twig', [
            'abonnements' => $abonnementsArray,
        ]);
    }

    /**
     * @Route("/new", name="abonnement_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $abonnement = new Abonnement();
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            $plan = $this->planRepository->find($form->getData()->getPlan());
            $dateEcheance = strtotime("now +".$plan->getDuree()." days");
            $abonnement->setDateEcheance(new \DateTime(date('Y-m-d H:i:s', $dateEcheance)));
            $abonnement->setTarif($plan->getTarif());

            $this->getDoctrine()->getManager();
            $entityManager->persist($abonnement);
            $entityManager->flush();

            return $this->redirectToRoute('create_paiement',['id'=>$abonnement->getId()]);
        }

        return $this->render('abonnement/new.html.twig', [
            'abonnement' => $abonnement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="abonnement_show", methods={"GET"})
     */
    public function show(Abonnement $abonnement): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('abonnement/show.html.twig', [
            'abonnement' => $abonnement,
        ]);
    }

    /**
     * @Route("/{id}/infos", name="abonnement_detail_admin", methods={"GET"})
     */
    public function infos($id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $restaurant = $this->restaurantRepository->find($id);
        return $this->render('abonnement/show.html.twig', [
            'abonnement' => $this->abonnementRepository->findOneBy(['restaurant'=>$restaurant]) ,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="abonnement_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Abonnement $abonnement): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createForm(AbonnementType::class, $abonnement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plan = $this->planRepository->find($form->getData()->getPlan());
            $dateEcheance = strtotime("now +".$plan->getDuree()." days");
            $abonnement->setDateEcheance(new \DateTime(date('Y-m-d H:i:s', $dateEcheance)));
            $abonnement->setTarif($plan->getTarif());

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('abonnement_index');
        }

        return $this->render('abonnement/edit.html.twig', [
            'abonnement' => $abonnement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="abonnement_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Abonnement $abonnement): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->isCsrfTokenValid('delete'.$abonnement->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($abonnement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('abonnement_index');
    }

    /**
     * @Route("/export-facture/{id}", name="abonnement_facture")
     */
    public function exporteFacture($id, GlobalService $global_s, AbonnementRepository $abonnementRepository){

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $abonnement = $abonnementRepository->find($id);
        $abonnementArray = [
            'data'=>$abonnement,
            'plan'=>[
                'nom'=>$abonnement->getPlan()
            ],
            'client'=>[
                'prenom'=> $abonnement->getRestaurant() ? (
                        count($abonnement->getRestaurant()->getUsers()) > 0 ? $abonnement->getRestaurant()->getUsers()[0]->getPrenom() : ""
                    ) :"",
                'nom'=> $abonnement->getRestaurant() ? (
                        count($abonnement->getRestaurant()->getUsers()) > 0 ? $abonnement->getRestaurant()->getUsers()[0]->getNom() : ""
                    ) : ""
            ],
            'restaurant'=>[
                'nom'=>$abonnement->getRestaurant() ? $abonnement->getRestaurant()->getNom() : "",
            ]
        ];
        
        $ouput_name = 'facture_'.$abonnement->getId().'.pdf';
        $params = [
            'format'=>['value'=>'A4', 'affichage'=>'portrait'],
            'is_download'=>['value'=>false, 'save_path'=>""]
        ];
        $dompdf = $global_s->generatePdf('abonnement/facture_pdf.html.twig', $abonnementArray , $params);  
        return new Response ($dompdf->stream($ouput_name, array("Attachment" => false)));
    }
}
