<?php

namespace App\Controller;

use App\Entity\SortieCaisse;
use App\Form\SortieCaisseType;
use App\Repository\SortieCaisseRepository;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/dashboad/sortie/caisse")
 */
class SortieCaisseController extends AbstractController
{

    public function __construct(Security $security)
    {
        // Avoid calling getUser() in the constructor: auth may not
        // be complete yet. Instead, store the entire Security object.
        $this->security = $security;
    }

    /**
     * @Route("/", name="sortie_caisse_index", methods={"GET"})
     */
    public function index(SortieCaisseRepository $sortieCaisseRepository): Response
    {
        return $this->render('sortie_caisse/index.html.twig', [
            'sortie_caisses' => $sortieCaisseRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="sortie_caisse_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $sortieCaisse = new SortieCaisse();
        $form = $this->createForm(SortieCaisseType::class, $sortieCaisse);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user = $this->security->getUser();
            $sortieCaisse->setRestaurant($user->getRestaurant());
            $entityManager->persist($sortieCaisse);
            $entityManager->flush();

            return $this->redirectToRoute('sortie_caisse_index');
        }

        return $this->render('sortie_caisse/new.html.twig', [
            'sortie_caisse' => $sortieCaisse,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="sortie_caisse_show", methods={"GET"})
     */
    public function show(SortieCaisse $sortieCaisse): Response
    {
        return $this->render('sortie_caisse/show.html.twig', [
            'sortie_caisse' => $sortieCaisse,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="sortie_caisse_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, SortieCaisse $sortieCaisse): Response
    {
        $form = $this->createForm(SortieCaisseType::class, $sortieCaisse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('sortie_caisse_index');
        }

        return $this->render('sortie_caisse/edit.html.twig', [
            'sortie_caisse' => $sortieCaisse,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="sortie_caisse_delete", methods={"DELETE"})
     */
    public function delete(Request $request, SortieCaisse $sortieCaisse): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sortieCaisse->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($sortieCaisse);
            $entityManager->flush();
        }

        return $this->redirectToRoute('sortie_caisse_index');
    }
}
