<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use App\Repository\CommandeProduitRepository;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Repository\RestaurantRepository;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Produit;
use App\Entity\Restaurant;
use App\Entity\Categorie;
use App\Entity\Stock;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;

use App\Service\ProduitService;
/**
 * API Controller.
 * @Route("/api/produit", name="api_produit_")
 */
class ProduitController extends APIController
{
    private $produitRepository;
    private $doctrine;
    private $restaurantRepository;
    private $categorieRepository;
    private $commandeProduitRepository;
    private $produit_s;

    public function __construct(ProduitRepository $produitRepository, RestaurantRepository $restaurantRepository, ProduitService $produit_s, CategorieRepository $categorieRepository, CommandeProduitRepository $commandeProduitRepository)
    {
        $this->produitRepository = $produitRepository;
        $this->restaurantRepository = $restaurantRepository;
        $this->commandeProduitRepository = $commandeProduitRepository;
        $this->categorieRepository = $categorieRepository;
        $this->produit_s = $produit_s;
    }

    /**
    * @Rest\Get("/get-by-categorie", name="get_by_categorie")
    *
    * @return Response
    */
    public function getProduitByCategory(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $produits = $this->produitRepository->findBy(['categorie'=>$request->get('cat_id'), 'is_delete'=>false]);
        $produitsArray = [];
        foreach ($produits as $key => $value) {
            $produit = $this->produitRepository->find($value->getId());
            if($value->getImage())
                $image = $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getImage();
            else
                $image = $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/image-default.jpeg";

            $produitsArray[] = [
                'id'=>$value->getId(),
                'name'=> $value->getNom(),
                'icon'=> $image,
                'qty_stock'=>$value->getQuantite(),
                'qty'=>0,
                'description'=> $value->getDescription(),
                'price'=>$value->getPrix(),
            ];
        }

        return $this->handleView($this->view(
            $produitsArray, 
            Response::HTTP_OK)
        );
    }


      /**
     *Get User profile info.
     * @Rest\Get("/get-by-user-restaurant", name="get_by_user_restaurant")
     *
     * @return Response
     */
    public function getByResto(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }
        $produits =  $this->produitRepository->findBy(['restaurant'=>$user->getRestaurant()->getId(), 'is_delete'=>false]);
        $produitsArray = [];
        foreach ($produits as $key => $value) {
            if($value->getImage())
                $image = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$value->getImage());
                 
            else
                $image = str_replace("index.php/", "", $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/image-default.jpeg");

            $produitsArray[] = [
                'id'=> $value->getId(),
                'name'=> $value->getNom(),
                'icon'=> $image,
                'price'=>$value->getPrix(),
                'description'=>$value->getDescription(),
                'qty_stock'=>$value->getQuantite(),
            ];
        }
        return $this->handleView($this->view($produitsArray, Response::HTTP_OK));
    }

      /**
     * @Rest\Get("/get-by-id", name="get_by_id")
     *
     * @return Response
     */
    public function getById(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }
        $produit = $this->produitRepository->find($request->get('product_id'));
        if($produit->getImage())
            $image = $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."uploads/produits/".$produit->getImage();
        else
            $image = $this->generateUrl('homepage', [], UrlGenerator::ABSOLUTE_URL)."images/image-default.jpeg";

        $produitArray = [
            'id'=> $produit->getId(),
            'name'=> $produit->getNom(),
            'icon'=> $image,
            'price'=>$produit->getPrix(),
            'description'=>$produit->getDescription(),
            'qty_stock'=>$produit->getQuantite(),
            'in_commande' =>  count($this->commandeProduitRepository->findBy(['produit'=>$produit->getId()])),
        ];
        
        return $this->handleView($this->view($produitArray, Response::HTTP_OK));
    }

      /**
     *Get User profile info.
     * @Rest\Get("/edit-stock", name="edit_stock")
     *
     * @return Response
     */
    public function editStock(Request $request)
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
        $produit = $this->produitRepository->find($request->get('product_id'));
        if($request->get('operation') == "reduction")
            $produit->setQuantite($produit->getQuantite() - $request->get('qty'));
        elseif ($request->get('operation') == "add") 
            $produit->setQuantite($produit->getQuantite() + $request->get('qty'));

        $entityManager->flush();
        return $this->handleView($this->view($produit->getId(), Response::HTTP_OK));
    }

    /**
     * @Rest\Post("/add", name="add_product")
     *
     * @return Response
     */
    public function addProduct(Request $request)
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

        $produit = new Produit();
        if($request->get('product_id'))
            $produit = $this->produitRepository->find($request->get('product_id'));

        $produit->setNom($request->get('nom'));
        $produit->setPrix($request->get('prix'));
        $produit->setCategorie($this->categorieRepository->find($request->get('categorie')));
        $produit->setRestaurant($this->restaurantRepository->find($user->getRestaurant()->getId()));
        $produit->setQuantite($request->get('quantite'));
        $produit->setDescription($request->get('description'));
        
        if ($request->get('image')) {
            $base64_string = $request->get('image');
            $nameImage = Date("Yds").".png";
            $savePath = $request->server->get('DOCUMENT_ROOT')."/uploads/produits/".$nameImage;
            $data = explode( ',', $base64_string );
            file_put_contents($savePath, base64_decode($data[1]));
            $produit->setImage($nameImage);
        }
        elseif ($request->get('image_url')) {
            $nameImage = Date("Yds").".png";
            $savePath = $request->server->get('DOCUMENT_ROOT')."/uploads/produits/".$nameImage;
            $ch = curl_init($request->get('image_url'));
            $fp = fopen($savePath, 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
        
        $entityManager->persist($produit);
        $entityManager->flush();

        return $this->handleView($this->view(
            $produit->getId(), 
            Response::HTTP_OK)
        );
    }


    /**
     * @Rest\Get("/delete", name="delete_produit")
     *
     * @return Response
     */
    public function deleteProduit(Request $request)
    {
        $user = $this->authToken($request);
        if (is_array($user)) {
            return $this->handleView(
                $this->view(
                    $user,
                    Response::HTTP_UNAUTHORIZED)
            );
        }

        $produit = $this->produitRepository->find($request->get('product_id'));
        $entityManager = $this->getDoctrine()->getManager();
        if(count($this->commandeProduitRepository->findBy(['produit'=>$produit->getId()])))
            $produit->setIsDelete(true);
        else
            $entityManager->remove($produit);

        $entityManager->flush();
        return $this->handleView($this->view(
            [
                'status' => 'success',
                'message' => "Produit supprimé"
            ], Response::HTTP_OK));
    }

}
