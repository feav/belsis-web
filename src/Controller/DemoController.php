<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    /**
     * @Route("/demo", name="demo")
     */
    public function index()
    {
        return $this->render('demo.html.twig', ['myName'=> "Jospin"]);
    }
}
