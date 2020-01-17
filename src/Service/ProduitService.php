<?php
namespace App\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\Produit;

class ProduitService{
    
    public function __construct(ManagerRegistry $registry, RegistryInterface $doctrine){
        $this->doctrine = $doctrine;
    }

    public function changeStock($action, $qty, $id){
        
        $produit = $this->doctrine->getRepository(Produit::class)->find($id);
        if($action == "add")
            $produit->setQuantite( $produit->getQuantite() + $qty);
        else
            $produit->setQuantite( $produit->getQuantite() - $qty);

        //$this->doctrine->getEntityManager()->flush();
        return $produit->getId();
    }

}
