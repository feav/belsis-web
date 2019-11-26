<?php

namespace App\Controller\Api;

use App\Entity\CommandeProduit;
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

/**
 * API Controller.
 * @Route("/api/commande", name="api_commande_")
 */
class CommandeController extends APIController
{
    /**
     *Get Commandes of a 'Restaurant'.
     * @Rest\Post("/get-all", name="get_all")
     *
     * @return Response
     */
    public function getCommandes(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->authToken($data['token']);
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
                    'statut' => 'error',
                    'message' => 'Cet utilisateur n\est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $restaurant->getId();
        $commandes = $restaurant->getCommandes();
        $res = [];
        foreach ($commandes as $k => $cmd) {
            $tabCommandes['id'] = $cmd->getId();
            $tabCommandes['code'] = $cmd->getCode();
            $tabCommandes['table'] = $cmd->getTable()->getNom();
            $cmdProduits = $cmd->getCommandeProduit();
            $total = 0;
            $produits = [];
            foreach ($cmdProduits as $n=>$cmdProduit){
                $total += $cmdProduit->getPrix() * $cmdProduit->getQuantite();
                $produits[$n]['nom'] = $cmdProduit->getProduit()->getNom();
                $produits[$n]['quantite'] = $cmdProduit->getQuantite();
                $produits[$n]['prix'] = $cmdProduit->getPrix();
                $produits[$n]['stock'] = $cmdProduit->getProduit()->getQuantite();
            }
            $tabCommandes['total'] = $total;
            $tabCommandes['produits'] = $produits;
            $res[] = $tabCommandes;
        }
        return $this->handleView($this->view($res, Response::HTTP_OK));
    }

    /**
     *Get Commandes of a table in a 'Restaurant'.
     * @Rest\Post("/get-by-table-id", name="get_by_table_id")
     *
     * @return Response
     */
    public function getCommandesByTableId(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->authToken($data['token']);
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
                    'statut' => 'error',
                    'message' => 'Cet utilisateur n\est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $restaurant->getId();

        if (empty($data['table_id'])) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Le champ table_id est vide.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $tableId = $data['table_id'];
        $table = $this->getDoctrine()->getRepository(Table::class)->findBy(['id'=>$tableId, 'restaurant' => $restoId]);
        if (empty($table)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Cette table n\'est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }

        $commandes = $this->getDoctrine()->getRepository(Commande::class)->findBy(['table'=>$tableId, 'restaurant' => $restoId]);
        $res = [];
        foreach ($commandes as $k => $cmd) {
            $tabCommandes['id'] = $cmd->getId();
            $tabCommandes['code'] = $cmd->getCode();
            $tabCommandes['table'] = $cmd->getTable()->getNom();
            $cmdProduits = $cmd->getCommandeProduit();
            $total = 0;
            $produits = [];
            foreach ($cmdProduits as $n=>$cmdProduit){
                $total += $cmdProduit->getPrix() * $cmdProduit->getQuantite();
                $produits[$n]['nom'] = $cmdProduit->getProduit()->getNom();
                $produits[$n]['quantite'] = $cmdProduit->getQuantite();
                $produits[$n]['prix'] = $cmdProduit->getPrix();
                $produits[$n]['stock'] = $cmdProduit->getProduit()->getQuantite();
            }
            $tabCommandes['total'] = $total;
            $tabCommandes['produits'] = $produits;
            $res[] = $tabCommandes;
        }
        return $this->handleView($this->view($res, Response::HTTP_OK));
    }

    /**
     *Get Commandes of a table in a 'Restaurant'.
     * @Rest\Post("/get", name="get")
     *
     * @return Response
     */
    public function getCommande(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $user = $this->authToken($data['token']);
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
                    'statut' => 'error',
                    'message' => 'Cet utilisateur n\'est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $restaurant->getId();

        if (empty($data['id'])) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Le champ id est vide'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $commandeId = $data['id'];

        $cmd = $this->getDoctrine()->getRepository(Commande::class)->findOneBy(['id'=>$commandeId, 'restaurant' => $restoId]);

        $res['id'] = $cmd->getId();
        $res['code'] = $cmd->getCode();
        $res['table'] = $cmd->getTable()->getNom();
        $cmdProduits = $cmd->getCommandeProduit();
        $total = 0;
        $produits = [];
        foreach ($cmdProduits as $n=>$cmdProduit){
            $total += $cmdProduit->getPrix() * $cmdProduit->getQuantite();
            $produits[$n]['nom'] = $cmdProduit->getProduit()->getNom();
            $produits[$n]['quantite'] = $cmdProduit->getQuantite();
            $produits[$n]['prix'] = $cmdProduit->getPrix();
            $produits[$n]['stock'] = $cmdProduit->getProduit()->getQuantite();
        }
        $res['total'] = $total;
        $res['produits'] = $produits;

        return $this->handleView($this->view($res, Response::HTTP_OK));
    }

    /**
     *Add new Order.
     * @Rest\Post("/new", name="new")
     *
     * @return Response
     */
    public function newCommande(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $produits = [];
        $user = $this->authToken($data['token']);
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
                    'statut' => 'error',
                    'message' => 'Cet utilisateur n\'est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $restaurant->getId();

        if (empty($data['table_id'])) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Le champ table_id est vide.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $tableId = $data['table_id'];

        $table = $this->getDoctrine()->getRepository(Table::class)->findOneBy(['id'=>$tableId, 'restaurant' => $restoId]);
        if (empty($table)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Cette table n\'existe pas.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }

        if (empty($data['produits'])) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Le champ produits est bien vide.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }else{
            if(is_array($data['produits']) && !empty($data['produits'])){
                $produits = [];
                $commande = new Commande();
                $commande->setCode('cmd123');
                $commande->setRestaurant($restaurant);
                $commande->setDate(new \DateTime());
                $commande->setEtat('en_cours');
                $commande->setTable($table);
                $commande->setUser($user);
                $this->em->persist($commande);
                $this->em->flush();
                $cmdProduits = [];
                foreach($data['produits'] as $produitData){
                    $produit = $this->getDoctrine()->getRepository(Produit::class)->findOneBy(['id'=>$produitData['id'], 'restaurant' => $restoId]);
                    if (!empty($produit)) {
                        $cmdProduit = new CommandeProduit();
                        $cmdProduit->setCommande($commande);
                        $cmdProduit->setQuantite($produitData['quantite']);
                        $cmdProduit->setPrix($produitData['prix']);
                        $cmdProduit->setProduit($produit);
                        $cmdProduits[] = $cmdProduit;
                        $this->em->persist($cmdProduit);
                        $this->em->flush();
                    }else{
                        return $this->handleView(
                            $this->view([
                                'statut' => 'error',
                                'message' => 'Le produit d\'id '.$produitData['id'].' n\'existe pas.'
                            ],
                                Response::HTTP_BAD_REQUEST
                            ));
                    }
                }
                //$commande->addCommandeProduit($cmdProduits);

            }else{
                return $this->handleView(
                    $this->view([
                        'statut' => 'error',
                        'message' => 'Le champ produits n\'est pas un tableau.'
                    ],
                        Response::HTTP_BAD_REQUEST
                    ));
            }
        }

        return $this->handleView($this->view(['status' => 'success', 'message' => 'ajout reussi'], Response::HTTP_OK));
    }

    /**
     *Delete Order.
     * @Rest\Post("/delete", name="delete")
     *
     * @return Response
     */
    public function deleteCommande(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $produits = [];
        $user = $this->authToken($data['token']);
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
                    'statut' => 'error',
                    'message' => 'Cet utilisateur n\'est dans aucun restaurant.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $restoId = $restaurant->getId();

        if (empty($data['id'])) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Le champ id est vide.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }

        $commande = $this->getDoctrine()->getRepository(Commande::class)->findOneBy(['id'=>$data['id'], 'restaurant' => $restoId]);

        if (empty($commande)) {
            return $this->handleView(
                $this->view([
                    'statut' => 'error',
                    'message' => 'Cette commande n\'existe pas.'
                ],
                    Response::HTTP_BAD_REQUEST
                ));
        }
        $commandeProduits = $commande->getCommandeProduit();
        foreach($commandeProduits as $cdmProduit){
            $this->em->remove($cdmProduit);
        }
        $this->em->remove($commande);
        $this->em->flush();

        return $this->handleView($this->view(['status' => 'success', 'message' => 'suppression reussie'], Response::HTTP_OK));
    }
}
