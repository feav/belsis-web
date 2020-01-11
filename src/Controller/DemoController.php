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
    	$this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        return $this->render('demo.html.twig', ['myName'=> "Jospin"]);
    }
}
