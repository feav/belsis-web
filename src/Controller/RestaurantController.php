<?php

namespace App\Controller;

use App\Entity\Appareil;
use App\Entity\Restaurant;
use App\Entity\User;
use App\Entity\User00;
use App\Form\RestaurantType;
use App\Repository\RestaurantRepository;
use App\Repository\CommandeRepository;
use Doctrine\Common\Util\Debug;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\GlobalService;

/**
 * @Route("/restaurant")
 */
class RestaurantController extends AbstractController
{
    private $global_s;
    private $commandeRepository;
    
    public function __construct(GlobalService $global_s, CommandeRepository $commandeRepository){
      $this->global_s = $global_s;
      $this->commandeRepository = $commandeRepository;
    }

    /**
     * @Route("/", name="restaurant_index", methods={"GET"})
     */
    public
    function index(RestaurantRepository $restaurantRepository): Response
    {   
        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $restaurantRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="restaurant_new", methods={"GET","POST"})
     */
    public
    function new(Request $request): Response
    {
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            /** @var UploadedFile $logo */
            $logo = $form['logo']->getData();
            if ($logo) {
                $fileUploader = new FileUploader($this->getParameter("image_directory")."/restaurant");
                $newFilename = $fileUploader->upload($logo);

                $restaurant->setLogo($newFilename);
            }

            $entityManager->persist($restaurant);
            $entityManager->flush();

            $flashBag = $this->get('session')->getFlashBag()->clear();
            $this->addFlash('success', 'Enregistrement réussit');
            return $this->redirectToRoute('restaurant_index');
        }

        return $this->render('restaurant/new.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="restaurant_show", methods={"GET"})
     */
    public function show(Restaurant $restaurant): Response
    {   
        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
            'nbrCommande' => $this->commandeRepository->getCommandeRestaurant($restaurant->getId())
        ]);
    }    

    /**
     * @Route("/change-status/{id}", name="restaurant_status", methods={"GET"})
     */
    public function changeStatus(Request $request, Restaurant $restaurant): Response
    {   
        if($restaurant->getStatus())
            $restaurant->setStatus(false);
        else
            $restaurant->setStatus(true);
        $this->getDoctrine()->getManager()->flush();
        return $this->redirectToRoute('restaurant_index');
    }

    /**
     * @Route("/{id}/edit", name="restaurant_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Restaurant $restaurant): Response
    {
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $logo */
            $logo = $form['logo']->getData();
            if ($logo) {
                $fileUploader = new FileUploader($this->getParameter("image_directory")."/restaurant");
                $newFilename = $fileUploader->upload($logo);

                $restaurant->setLogo($newFilename);
            }
            $this->getDoctrine()->getManager()->flush();

            $flashBag = $this->get('session')->getFlashBag()->clear();
            $this->addFlash('success', 'Modification réussite');
            return $this->redirectToRoute('restaurant_index');
        }

        return $this->render('restaurant/edit.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="restaurant_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Restaurant $restaurant): Response
    {
        if ($this->isCsrfTokenValid('delete' . $restaurant->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($restaurant);
            $entityManager->flush();

            $flashBag = $this->get('session')->getFlashBag()->clear();
            $this->addFlash('success', 'Suppression réussite');
        }

        return $this->redirectToRoute('restaurant_index');
    }
}
