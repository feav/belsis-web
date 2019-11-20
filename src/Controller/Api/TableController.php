<?php

namespace App\Controller\Api;

use App\Entity\Commande;
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
    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/get-all", name="get_all")
     *
     * @return Response
     */
    public function getTables(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->authToken($data['token']);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }

        $restaurant = $user->getRestaurant();
        if (empty($restaurant)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Cet utilisateur n\est dans aucun restaurant.'
                ],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ));
        }
        $restoId = $restaurant->getId();
        $tables = $restaurant->getTables();
        $res = [];
        foreach ($tables as $k => $table) {
            $tabTables['id'] = $table->getId();
            $tabTables['nom'] = $table->getNom();
            $tabTables['coord_x'] = $table->getCoordX();
            $tabTables['coord_y'] = $table->getCoordY();
            //$tabTables['serveur'] = $table->getUser();

            $cmd = $this->getDoctrine()->getRepository(Commande::class)->findBy(['table' => $table->getId(), 'etat' => "en_cours"]);

            if(empty($cmd)){
                $tabTables['nb_commandes'] = 0;
            }else{
                $tabTables['nb_commandes'] = sizeof($cmd);
            }

            $res[] = $tabTables;
        }
        return $this->handleView($this->view($res, 200));
    }
}
