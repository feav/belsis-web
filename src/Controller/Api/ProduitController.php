<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Entity\Restaurant;
use App\Entity\Categorie;
use App\Entity\Stock;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * API Controller.
 * @Route("/api/produit", name="api_produit_")
 */
class ProduitController extends APIController
{

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/get-all", name="get_all")
     *
     * @return Response
     */
    public function getProduits(Request $request)
    {
        var_dump($request->headers->get('Authorization')); die();
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }

        $user = $this->authToken($data['token']);


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
        $produits = $restaurant->getProduits();
        $res = [];
        foreach ($produits as $k => $produit) {
            $tabProduits['id'] = $produit->getId();
            $tabProduits['nom'] = $produit->getNom();
            $tabProduits['image'] = "image.jpg";
            $tabProduits['categorie'] = $produit->getCategorie()->getNom();

            $stock = $produit->getStock();

            //on suppose qu'il y'a un seul stock pour l'instant
            $tabProduits['quantite'] = $produit->getQuantite();

            $res[] = $tabProduits;
        }
        return $this->handleView($this->view($res, 200));
    }

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/get-by-categorie-id", name="get_by_categorie_id")
     *
     * @return Response
     */
    public function getProduitsByCategorie(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
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
        if (empty($data['categorie_id'])) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Le champ categorie_id est vide'
                ],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ));
        }
        $categorie = $this->getDoctrine()->getRepository(Categorie::class)->find($data['categorie_id']);
        if (empty($categorie)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Cette catégorie n\'existe pas'
                ],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ));
        }
        $produits = $categorie->getProduits();
        $res = [];
        foreach ($produits as $k => $produit) {
            $tabProduits['id'] = $produit->getId();
            $tabProduits['nom'] = $produit->getNom();
            $tabProduits['prix'] = $produit->getPrix();
            $tabProduits['image'] = "image.jpg";
            $tabProduits['categorie'] = $produit->getCategorie()->getNom();

            $stock = $produit->getStock();

            //on suppose qu'il y'a un seul stock pour l'instant
            $tabProduits['quantite'] = $produit->getQuantite();

            $res[] = $tabProduits;
        }
        return $this->handleView($this->view($res, 200));
    }

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/new", name="new")
     *
     * @return Response
     */
    public function newProduit(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_INTERNAL_SERVER_ERROR)
            );
        }
        if (empty($data["nom"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ nom est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data["prix"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ prix est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data["quantite"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ quantite est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data["categorie_id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ categorie_id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        if (empty($data["image"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ image est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
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

        $categorie = $this->getDoctrine()->getRepository(Categorie::class)->find($data['categorie_id']);

        $stock = new Stock();
        $stock->setRestaurant($restaurant);
        $stock->setNom($data['nom']);
        $stock->setQuantite($data['quantite']);
        $this->em->persist($stock);

        $produit = new Produit();
        $produit->setRestaurant($restaurant);
        $produit->setNom($data['nom']);
        $produit->setPrix($data['prix']);
        $produit->setCategorie($categorie);
        $produit->addStock($stock);

        $this->em->persist($produit);

        $this->em->flush();

        return $this->handleView($this->view(['status' => 'success', 'message' => 'insertion reussie'], 200));
    }

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/update", name="update")
     *
     * @return Response
     */
    public function updateProduit(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
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

        if (empty($data["id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $produit = $this->getDoctrine()->getRepository(Produit::class)->find($data['id']);
        if (empty($produit)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Ce produit n\'existe pas'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        if (!empty($data["categorie_id"])) {
            $categorie = $this->getDoctrine()->getRepository(Categorie::class)->find($data['categorie_id']);
            $produit->setCategorie($categorie);
        }

        if (!empty($data["quantite"])) {
            $produit->setQuantite($data['quantite']);
        }
        if (!empty($data["nom"])) {
            $produit->setNom($data["nom"]);
        }
        if (!empty($data["prix"])) {
            $produit->setPrix($data["prix"]);
        }

        if (!empty($data["image"])) {
            $produit->setImage($data["image"]);
        }
        $this->em->persist($produit);
        $this->em->flush();

        return $this->handleView($this->view(['status' => 'success', 'message' => 'insertion reussie'], 200));
    }

    /**
     *Get Product of a 'Restaurant'.
     * @Rest\Post("/get", name="get")
     *
     * @return Response
     */
    public function getProduit(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
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
        if (empty($data["id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $produitId = $data["id"];

        $produit = $this->getDoctrine()->getRepository(Produit::class)->findOneBy(['id' => $produitId, 'restaurant' => $restoId]);
        if (empty($produit)) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Aucun produit n\'existe avec cet identifiant'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }

        $res = [];
        $res['id'] = $produit->getId();
        $res['nom'] = $produit->getNom();
        $res['image'] = 'image.jpg';
        $res['prix'] = $produit->getPrix();
        $res['quantite'] = $produit->getQuantite();
        $res['categorie'] = $produit->getCategorie()->getNom();

        return $this->handleView($this->view($res, 200));
    }

    /**
     *Get Product af a 'Restaurant'.
     * @Rest\Post("/delete", name="delete")
     *
     * @return Response
     */
    public function deleteProduit(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $user = $this->authToken($request->get('token'));
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

        if (empty($data["id"])) {
            return $this->handleView(
                $this->view([
                    'status' => 'error',
                    'message' => 'Le champ id est vide'
                ], RESPONSE::HTTP_BAD_REQUEST
                ));
        }
        $produitId = $data["id"];

        $produit = $this->getDoctrine()->getRepository(Produit::class)->findOneBy(['id' => $produitId, 'restaurant' => $restoId]);
        $this->em->remove($produit);
        $this->em->flush();

        return $this->handleView($this->view(['status' => 'success', 'message' => 'suppression reussie'], 200));
    }
}
