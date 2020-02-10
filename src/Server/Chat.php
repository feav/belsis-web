<?php

namespace App\Server;

use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use App\Entity\Discussion;
use App\Entity\Message;
use App\Entity\User;

use Symfony\Bridge\Doctrine\RegistryInterface;

class Chat implements MessageComponentInterface
{
    private $clients;

    private $users = [];

    private $botName = 'ChatBot';
    
    private $defaultChannel = 'general';

    private $em;

    public function __construct($em)
    {
        $this->em = $em;
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $this->users[$conn->resourceId] = [
            'connection' => $conn,
            'user' => '',
            'channels' => []
        ];
        echo sprintf('New connection: Hello #%d', $conn->resourceId);
    }

    public function onClose(ConnectionInterface $closedConnection)
    {
        // Suppression de la connexion des utilisateurs
        unset($this->users[$closedConnection->resourceId]);

        $this->clients->detach($closedConnection);
        echo sprintf('Connection #%d has disconnected\n', $closedConnection->resourceId);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->send('An error has occurred: '.$e->getMessage());
        $conn->close();
    }

    public function onMessage(ConnectionInterface $conn, $message)
    {
        $messageData = json_decode($message);
        if ($messageData === null) {
            return false;
        }
        $user = $this->em->getRepository(User::class)->find($messageData->userId);
        $action = $messageData->action ?? 'unknown';
        $channel = $messageData->channel ?? $this->defaultChannel;
        $message = $messageData->message ?? '';
        $date = new \DateTime();

        switch ($action) {
            case 'subscribe':
                $this->subscribeToChannel($conn, $channel, $user->getUsername());
                return $this->sendMessageToChannel($conn, $channel, $user->getId(), $message, $date, $action);
            case 'unsubscribe':
                $this->unsubscribeFromChannel($conn, $channel, $user->getUsername());
                return true;
            case 'message':
                $discussion = $this->em->getRepository(Discussion::class)->find($channel);
                $msg = New Message();
                $msg->setContenu($message);
                $msg->setDiscussion($discussion);
                $msg->setDateCreate($date);
                $msg->setDestinateur($user);
                $msg->setIsRead(false);
                
                $this->em->persist($msg);
                //$this->em->flush();
                
                return $this->sendMessageToChannel($conn, $channel, $user->getId(), $message, $date, $action);
            default:
                echo sprintf('L\'action "%s" n\'est pas supportée!', $action);
                break;
        }
        return false;
    }

    private function subscribeToChannel(ConnectionInterface $conn, $channel, $user)
    {
        $this->users[$conn->resourceId]['channels'][$channel] = $channel;
        echo sprintf($user.' a rejoint la discussion qui a pour sujet : #'.$channel);
    }

    private function unsubscribeFromChannel(ConnectionInterface $conn, $channel, $user)
    {
        if (array_key_exists($channel, $this->users[$conn->resourceId]['channels'])) {
            unset($this->users[$conn->resourceId]['channels']);
        }
        echo sprintf($user.' a quitté la discussion qui a pour sujet : #'.$channel);
    }

    private function sendMessageToChannel(ConnectionInterface $conn, $channel, $userId, $message, $date, $action)
    {
        if (!isset($this->users[$conn->resourceId]['channels'][$channel])) {
            return false;
        }
        foreach ($this->users as $connectionId => $userConnection) {
            if (array_key_exists($channel, $userConnection['channels'])) {
                $userConnection['connection']->send(json_encode([
                    'action' => $action,
                    'channel' => $channel,
                    'userId' => $userId,
                    'message' => $message,
                    'date' => $date->format('Y-m-d H:i') ?? (new \DateTime())->format('Y-m-d H:i'),
                ]));
            }
        }
        return true;
    }

}