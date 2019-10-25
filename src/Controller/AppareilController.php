<?php

namespace App\Controller;

use App\Entity\Appareil;
use App\Form\AppareilType;
use App\Repository\AppareilRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/appareil")
 */
class AppareilController extends AbstractController
{
    /**
     * @Route("/", name="appareil_index", methods={"GET"})
     */
    public function index(AppareilRepository $appareilRepository): Response
    {
        return $this->render('appareil/index.html.twig', [
            'appareils' => $appareilRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="appareil_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $appareil = new Appareil();
        $form = $this->createForm(AppareilType::class, $appareil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($appareil);
            $entityManager->flush();

            return $this->redirectToRoute('appareil_index');
        }

        return $this->render('appareil/new.html.twig', [
            'appareil' => $appareil,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="appareil_show", methods={"GET"})
     */
    public function show(Appareil $appareil): Response
    {
        return $this->render('appareil/show.html.twig', [
            'appareil' => $appareil,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="appareil_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Appareil $appareil): Response
    {
        $form = $this->createForm(AppareilType::class, $appareil);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('appareil_index');
        }

        return $this->render('appareil/edit.html.twig', [
            'appareil' => $appareil,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="appareil_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Appareil $appareil): Response
    {
        if ($this->isCsrfTokenValid('delete'.$appareil->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($appareil);
            $entityManager->flush();
        }

        return $this->redirectToRoute('appareil_index');
    }
}
