<?php

namespace App\Controller;

use App\Entity\Paiement;
use App\Form\PaiementType;
use App\Repository\PaiementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\AbonnementRepository;

/**
 * @Route("/paiement")
 */
class PaiementController extends AbstractController
{
    private $abonnementRepository;
    
    public function __construct(AbonnementRepository $abonnementRepository){
      $this->abonnementRepository = $abonnementRepository;
    }

    /**
     * @Route("/new", name="paiement_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $paiement = new Paiement();
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($paiement);
            $entityManager->flush();

            return $this->redirectToRoute('paiement_index');
        }

        return $this->render('paiement/new.html.twig', [
            'paiement' => $paiement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="paiement_index", methods={"GET"})
     */
    public function index(PaiementRepository $paiementRepository, $id = null): Response
    {   
        if (!is_null($id)) {
            $paiements = $this->abonnementRepository->find($id)->getPaiements();
        }
        else
            $paiements = $paiementRepository->findAll();

        $paiementsArray = [];
        foreach ($paiements as $key => $value) {
            $abonnement = $value->getAbonnement();
            $paiementsArray[] =[
                'data'=>$value,
                'getDateEcheance'=> $abonnement->getDateEcheance()->format('Y-m-d H:i:s'),
                'echeance'=>[
                    'month'=>(new \DateTime())->diff($abonnement->getDateEcheance())->format('%m'),
                    'day'=>(new \DateTime())->diff($abonnement->getDateEcheance())->format('%d'),
                    'hour'=>(new \DateTime())->diff($abonnement->getDateEcheance())->format('%H'),
                    'minute'=>(new \DateTime())->diff($abonnement->getDateEcheance())->format('%i'),
                    'is_expire'=> (strtotime($abonnement->getDateEcheance()->format('Y-m-d H:i:s')) - time()),
                ]
            ];
        }
        return $this->render('paiement/index.html.twig', [
            'paiements' => $paiementsArray,
        ]);
    }

    /**
     * @Route("/{id}/create", name="create_paiement", methods={"GET"})
     */
    public function create(Request $request, $id): Response
    {
        $abonnement = $this->abonnementRepository->find($id);
        $paiement = new Paiement();
        $paiement->setAbonnement($abonnement);
        $paiement->setMontant($abonnement->getTarif());
        $paiement->setDateCreation($abonnement->getDateCreation());
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($paiement);
        $entityManager->flush();

        return $this->redirectToRoute('abonnement_index');
    }

    /**
     * @Route("/{id}/detail", name="paiement_show", methods={"GET"})
     */
    public function show(Paiement $paiement): Response
    {
        return $this->render('paiement/show.html.twig', [
            'paiement' => $paiement,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="paiement_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Paiement $paiement): Response
    {
        $form = $this->createForm(PaiementType::class, $paiement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('paiement_index');
        }

        return $this->render('paiement/edit.html.twig', [
            'paiement' => $paiement,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/delete", name="paiement_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Paiement $paiement): Response
    {
        if ($this->isCsrfTokenValid('delete'.$paiement->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($paiement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('paiement_index');
    }
}
