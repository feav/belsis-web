<?php
namespace App\Service;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Templating\EngineInterface;
use Doctrine\{
    Common\Persistence\ObjectManager
};

class GlobalService{

    private $requestStack;
    private $public_path;
    private $templating;
    
    public function __construct(ObjectManager $em, RequestStack $requestStack, EngineInterface $templating){
        $this->em = $em;
        $this->request = $requestStack->getCurrentRequest();
        $this->public_path = $this->request->server->get('DOCUMENT_ROOT');
        $this->templating = $templating;
    }

    public function leftTimeFormate($date)
    {
        $retour = array();

        $date2 = time();
        $date1 = strtotime($date);

        $diff = ($date1 - $date2);

        if ($diff < 0) {
            $retour['negatif'] = true;
            $message = "etait il y a ";
        } else {
            $retour['negatif'] = false;
            $message = "dans ";
        }

        $tmp = abs($diff);
        $retour['second'] = $tmp % 60;

        $tmp = floor(($tmp - $retour['second']) / 60);
        $retour['minute'] = $tmp % 60;

        $tmp = floor(($tmp - $retour['minute']) / 60);
        $retour['hour'] = $tmp % 60;

        $tmp = floor(($tmp - $retour['hour']) / 24);
        $retour['day'] = $tmp % 24;
        $retour['message'] = $message;

        $tmp = floor(($tmp - $retour['day']) / 30);
        $retour['month'] = $diff % (60*60*3600*24*30);
        $retour['message'] = $message;

        return $retour;
    }
}
