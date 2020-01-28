<?php
namespace App\Service;

use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use App\Repository\UserRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Bridge\Doctrine\RegistryInterface;
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Notification;

class FirebaseService{

    private $doctrine;
    private $security;
    private $event;
    private $em;
    private $userRepository;
    private $server_key = 'AAAAEwOp8M0:APA91bHSRffKVzbdCPbvJkOe1DDrYu3HjRntnSyvTCQqKby8W0PNiPAdOIAhnJWyU68GPp2GvfJhzIMYJNWhO8rWC5vcnxzdJAkMCFAIed1zQ-7Kt3CKt8GooWTHDkS93wzFX__nYqzk';
    
    public function __construct(Security $security, UserRepository $userRepository){
        $this->security = $security;
        $this->userRepository = $userRepository;
    }

    public function pushNotification($tabUser, $title, $message, $topic=null, $tabData=[])
    {
        $client = new Client();
        $client->setApiKey($this->server_key);
        $client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

        $message = new Message();
        $message->setPriority('high');
        foreach ($tabUser as $user) {
            $message->addRecipient(new Device($user->getDeviceToken()));
        }
        $message
            ->setNotification(new Notification($title, $message))
            ->setData(['key' => 'value'])
        ;
        $response = $client->send($message);
        /*var_dump($response->getBody()->getContents());*/

        return $response;
    }

}
