<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class UserService{

    private $doctrine;
    private $security;
    private $event;
    private $em;
    
    public function __construct(Security $security){
        $this->security = $security;
        //$this->event = $event;
    }
    /*public function disconnect(){
            $session = $event->getRequest()->getSession();
            $session->invalidate();
            return 1;
    }*/
   
    public function getSuperRole(array $roles){
        $tabRole = array('5'=>'ROLE_SUPER_ADMIN', '4'=>'ROLE_ADMIN', '3'=>'ROLE_USER');
        $tabLevel= array();
        foreach ($tabRole as $key => $value) {
            if(in_array(strtoupper($value), $roles, true))
                $tabLevel[] = (int)$key;
        }
        return $tabRole[''.max($tabLevel)];
    }
}
