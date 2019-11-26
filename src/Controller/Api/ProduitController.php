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
        $tabProduits = [];
        $res = [];

        foreach ($produits as $k => $produit) {
            $tabProduits[] = $this->getInfos($produit);
            // $stock = $produit->getStock();
        }
        $res[] = $tabProduits;
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
        $tabProduits = [];
        foreach ($produits as $k => $produit) {
            $tabProduits[] = $this->getInfos($produit);

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

        $base64 = $this->checkBase64($data['image']);
        $imageName = 'produit' . '-' . uniqid() . '.' . $base64['type'];
        file_put_contents($imageName, $base64['data']);

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
        if(empty($data['stocks'])  ){
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Le champ stocks est vide ou n\'est pas un tableau!'
                ],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                ));
        }
        $stocks = $data['stocks'];
        $stockObjs = [];
        $produit = new Produit();
        $produit->setRestaurant($restaurant);
        $produit->setNom($data['nom']);
        $produit->setPrix($data['prix']);
        $produit->setCategorie($categorie);
        $produit->setImage($imageName);
        foreach ($stocks as $stock){
            $stockObj = $this->getDoctrine()->getRepository(Stock::class)->find($stock['id']);
            $produit->addStock($stockObj);
        }

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

        $res = $this->getInfos($produit);

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

    public function getInfos(Produit $produit)
    {
        $base64 = "";
        if ($produit->getImage()) {
            $path = 'uploads/produits/' . $produit->getImage();
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        return [
            'id' => $produit->getId(),
            'nom' => $produit->getNom(),
            'prix' => $produit->getPrix(),
            'quantite' => $produit->getQuantite(),
            'categorie' => $produit->getCategorie()->getNom(),
            'image' => $base64
        ];
    }

    protected function checkBase64($data){
        if (preg_match('/^data:image\/(\w+);base64,/', $data, $type)) {
            $data = substr($data, strpos($data, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                throw new \Exception('invalid image type');
            }

            $data = base64_decode($data);

            if ($data === false) {
                throw new \Exception('base64_decode failed');
            }

            return ["data" => $data, "type" => $type];
        } else {
            throw new \Exception('did not match data URI with image data');
        }
    }

}
