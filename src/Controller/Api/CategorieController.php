<?php

namespace App\Controller\Api;

use App\Entity\Categorie;
use App\Entity\Commande;
use App\Entity\User;
use App\Entity\Restaurant;
use App\Form\RestaurantType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * API Controller.
 * @Route("/api/categorie", name="api_categorie")
 */
class CategorieController extends APIController
{
    /**
     *Get User profile info.
     * @Rest\Post("/get", name="get")
     *
     * @return Response
     */
    public function getCategorie(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $restaurant = $user->getRestaurant();
        if (empty($restaurant)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Cet utilisateur n\est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $restaurant->getId();

        if (empty($data['id'])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ id est vide.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }

        $categorie = $this->getDoctrine()->getRepository(Categorie::class)->findOneBy(['id' => $data['id'], 'restaurant' => $restoId]);
        if (empty($categorie)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Aucune categorie ayant ce nom n\'est ebregistree'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        $res = $this->getInfos($categorie);

        return $this->handleView($this->view($res, Response::HTTP_OK));
    }

    /**
     *Get User profile info.
     * @Rest\Post("/get-all", name="get-all")
     *
     * @return Response
     */
    public function getAll(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $restaurant = $user->getRestaurant();
        if (empty($restaurant)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Cet utilisateur n\'est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $restaurant->getId();

        $categories = $this->getDoctrine()->getRepository(Categorie::class)->findBy(['restaurant' => $restoId]);
        if (empty($categories)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Aucune categorie trouvee'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        foreach ($categories as $categorie) {
            $res[] = $this->getInfos($categorie);
        }

        return $this->handleView($this->view($res, Response::HTTP_OK));
    }

    protected function getInfos(Categorie $categorie)
    {
        $catProduits = $categorie->getProduits();
        $produits = [];
        foreach ($catProduits as $n => $catProduit) {
            $produits[$n]['id'] = $catProduit->getId();
            $produits[$n]['nom'] = $catProduit->getNom();
            $produits[$n]['quantite'] = $catProduit->getQuantite();
            $produits[$n]['prix'] = $catProduit->getPrix();
        }
        $base64 = "";
        if ($categorie->getImage()) {
            $path = 'uploads/categories/' . $categorie->getImage()->getName();
            $type = pathinfo($path, PATHINFO_EXTENSION);
            if(file_exists($path)){
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        return [
            "id" => $categorie->getId(),
            "nom" => $categorie->getNom(),
            "description" => $categorie->getNom(),
            "produits" => $produits,
            "image" => $base64
        ];
    }
}
