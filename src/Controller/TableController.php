<?php

namespace App\Controller;

use App\Entity\Table;
use App\Form\TableType;
use App\Repository\TableRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/dashboad/table")
 */
class TableController extends AbstractController
{
    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/", name="table_index", methods={"GET"})
     */
    public function index(TableRepository $tableRepository): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        if($user->getRole() == "superadmin")
            $tables = $tableRepository->findAll();
        elseif($this->getUser()->getRole() == "admin")
            $tables = $tableRepository->findBy(['restaurant'=>$user->getRestaurant()]);

        return $this->render('table/index.html.twig', [
            'tables' => $tableRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="table_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $table = new Table();
        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user = $this->security->getUser();
            $table->setRestaurant($user->getRestaurant());
            $entityManager->persist($table);
            $entityManager->flush();

            return $this->redirectToRoute('table_index');
        }

        return $this->render('table/new.html.twig', [
            'table' => $table,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="table_show", methods={"GET"})
     */
    public function show(Table $table): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('table/show.html.twig', [
            'table' => $table,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="table_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Table $table): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createForm(TableType::class, $table);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('table_index');
        }

        return $this->render('table/edit.html.twig', [
            'table' => $table,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="table_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Table $table): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->isCsrfTokenValid('delete'.$table->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($table);
            $entityManager->flush();
        }

        return $this->redirectToRoute('table_index');
    }
}
