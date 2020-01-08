<?php

namespace App\Controller\Api;

use App\Entity\CommandeProduit;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\CategorieRepository;
use App\Repository\CommandeRepository;
use App\Repository\CommandeProduitRepository;
use Symfony\Component\Routing\Generator\UrlGenerator;
use App\Entity\Produit;
use App\Entity\Table;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Commande;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * API Controller.
 * @Route("/api/commande", name="api_order_")
 */
class CommandeController extends APIController
{
    private $commandeRepository;
    private $commandeProduitRepository;
    private $doctrine;

    public function __construct(CommandeRepository $commandeRepository, CommandeProduitRepository $commandeProduitRepository)
    {
        $this->commandeRepository = $commandeRepository;
        $this->commandeProduitRepository = $commandeProduitRepository;
    }

    /**
     *Get Commandes id.
     * @Rest\Post("/delete", name="delete_order")
     *
     * @return Response
     */
    public function deleteCommande(Request $request)
    {
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $entityManager = $this->getDoctrine()->getManager();
        $commande = $this->commandeRepository->find($request->get('order_id'));
        $entityManager->remove($commande);
        $entityManager->flush();

        return $this->handleView($this->view(
            [
                'status' => 'success',
                'message' => "Commande supprimé avec succès"
            ], 
            Response::HTTP_OK)
        );
    }


    /**
     *Get Commandes id
     * @Rest\Post("/get", name="get_commande")
     *
     * @return Response
     */
    public function getCommandeById(Request $request)
    {
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $commande = $this->commandeRepository->find($request->get('order_id'));
        $commandeProduit = $this->commandeProduitRepository->findBy(['commande'=>$request->get('order_id')]);

        $commandeProduitArray = [];
        $totalProduit = $totalPrice =0;
        foreach ($commandeProduit as $key => $value) {
            $commandeProduitArray[] = [
                'name'=> $value->getProduit()->getNom(),
                'icon'=> $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getProduit()->getImage(),
                'price'=>$value->getPrix(),
                'total_price'=>$value->getPrix() * $value->getQuantite(),
                'qty'=>$value->getQuantite()
            ];
            $totalProduit += $value->getQuantite();
            $totalPrice += $value->getPrix() * $value->getQuantite();
        }

        return $this->handleView($this->view(
            [
                'token'=> $request->get('token'),
                'id'=> $commande->getId(),
                'data'=> $commande->getDate()->format('Y-m-d H:i:s'),
                'etat'=> $commande->getEtat(),
                'price'=> $totalPrice,
                'qty'=> $totalProduit,
                'detail'=> $commandeProduitArray
            ], 
            Response::HTTP_OK)
        );
    }

}
