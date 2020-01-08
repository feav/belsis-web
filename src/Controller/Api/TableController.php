<?php

namespace App\Controller\Api;

use App\Entity\Commande;
use App\Repository\TableRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * API Controller.
 * @Route("/api/table", name="api_table_")
 */
class TableController extends APIController
{
    private $tableRepository;
    private $doctrine;

    public function __construct(TableRepository $tableRepository)
    {
        $this->tableRepository = $tableRepository;
    }

    /**
    * Get table by shop
    * @Rest\Post("/get-by-shop", name="get_by_shop")
    *
    * @return Response
    */
    public function getAllTableOfMyShop(Request $request)
    {
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $tables = $this->tableRepository->findBy(['restaurant'=>$user->getRestaurant()->getId()]);
        $tablesArray = [];
        foreach ($tables as $key => $value) {
            $tablesArray[] = [
                'id'=>$value->getId(),
                'name'=> $value->getNom(),
                'description'=> $value->getDescription(),
                'coord_x'=> $value->getCoordX(),
                'coord_y'=> $value->getCoordY()
            ];
        }

        return $this->handleView($this->view(
            $tablesArray, 
            Response::HTTP_OK)
        );
    }

}
