<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use App\Repository\CommandeProduitRepository;
use App\Service\FileUploader;
use Behat\Transliterator\Transliterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/dashboad/produit")
 */
class ProduitController extends AbstractController
{   
    private $commandeProduitRepository;
    public function __construct(Security $security, CommandeProduitRepository $commandeProduitRepository)
    {
        $this->security = $security;
        $this->commandeProduitRepository = $commandeProduitRepository;
    }

    /**
     * @Route("/", name="produit_index", methods={"GET"})
     */
    public function index(ProduitRepository $produitRepository): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();
        if($user->getRole() == "superadmin")
            $produits = $produitRepository->findBy(['is_delete'=>false]);
        elseif($this->getUser()->getRole() == "admin")
            $produits = $produitRepository->findBy(['restaurant'=>$user->getRestaurant(), 'is_delete'=>false]);
        
        return $this->render('produit/index.html.twig', [
            'produits' => $produits
        ]);
    }

    /**
     * @Route("/new", name="produit_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            /** @var UploadedFile $image */
            $image = $form['image']->getData();
            if ($image) {
                $fileUploader = new FileUploader($this->getParameter("produits_directory"));
                $newFilename = $fileUploader->upload($image);

                $produit->setImage($newFilename);
            }
            $user = $this->security->getUser();
            $produit->setRestaurant($user->getRestaurant());
            $entityManager->persist($produit);
            $entityManager->flush();

            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="produit_show", methods={"GET"})
     */
    public function show(Produit $produit): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
            'is_in_commande' =>  count($this->commandeProduitRepository->findBy(['produit'=>$produit->getId()]))
        ]);
    }

    /**
     * @Route("/{id}/edit", name="produit_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Produit $produit): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $image */
            $image = $form['image']->getData();
            if ($image) {
                $fileUploader = new FileUploader($this->getParameter("produits_directory"));
                $newFilename = $fileUploader->upload($image);

                $produit->setImage($newFilename);
            }
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('produit_index');
        }

        return $this->render('produit/edit.html.twig', [
            'produit' => $produit,
            'is_in_commande' =>  count($this->commandeProduitRepository->findBy(['produit'=>$produit->getId()])),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="produit_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Produit $produit): Response
    {   
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        if ($this->isCsrfTokenValid('delete' . $produit->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            if(count($this->commandeProduitRepository->findBy(['produit'=>$produit->getId()])))
                $produit->setIsDelete(true);
            else
                $entityManager->remove($produit);

            $entityManager->flush();
            $this->addFlash('success', 'Suppression réussite');
        }

        return $this->redirectToRoute('produit_index');
    }

}
