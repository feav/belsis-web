<?php

namespace App\Controller\Api;

use App\Entity\Table;
use App\Repository\TableRepository;
use App\Repository\RestaurantRepository;
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
    private $restaurantRepository;
    private $doctrine;

    public function __construct(TableRepository $tableRepository, RestaurantRepository $restaurantRepository)
    {
        $this->tableRepository = $tableRepository;
        $this->restaurantRepository = $restaurantRepository;
    }

    /**
    * Get table by shop
    * @Rest\Get("/get-by-shop", name="get_by_shop")
    *
    * @return Response
    */
    public function getAllTableOfMyShop(Request $request)
    {
        $user = $this->authToken($request);
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
                'coord_y'=> $value->getCoordY(),
                'commandes'=> count($value->getCommandes())
            ];
        }

        return $this->handleView($this->view(
            $tablesArray, 
            Response::HTTP_OK)
        );
    }

    /**
     * @Rest\Post("/add", name="add_table")
     *
     * @return Response
     */
    public function addTable(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $entityManager = $this->getDoctrine()->getManager();

        $table = new Table();
        if($request->get('table_id'))
            $table = $this->tableRepository->find($request->get('table_id'));

        $table->setNom($request->get('nom'));
        $table->setDescription($request->get('description'));
        $table->setNumero($request->get('numero'));
        $table->setRestaurant($this->restaurantRepository->find($request->get('restaurant')));
        
        $entityManager->persist($table);
        $entityManager->flush();

        return $this->handleView($this->view(
            $table->getId(), 
            Response::HTTP_OK)
        );
    }

}
