<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index()
    {
        return $this->render('accueil.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/accueil", name="accueil")
     */
    public function home()
    {
        return $this->render('accueil.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }
}
