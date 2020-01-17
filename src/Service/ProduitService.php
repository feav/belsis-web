<?php
namespace App\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Security;
use App\Repository\ProduitRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\Produit;

class ProduitService{
    
    private $produitRepository;
    public function __construct(ManagerRegistry $registry, ProduitRepository $produitRepository){
        $this->produitRepository = $produitRepository;
    }

    public function changeStock($action, $qty, $id){
        
        $produit = $this->produitRepository->find($id);
        if($action == "add")
            $produit->setQuantite( $produit->getQuantite() + $qty);
        else
            $produit->setQuantite( $produit->getQuantite() - $qty);

        //$this->doctrine->getEntityManager()->flush();
        return $produit->getId();
    }

}
