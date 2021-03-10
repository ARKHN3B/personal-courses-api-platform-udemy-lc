<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Customer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class CustomerUserSubscriber implements EventSubscriberInterface {
  private $security;

  public function __construct(Security $security) {
    $this->security = $security;
  }

  public function onKernelView(ViewEvent $event) {
    $result = $event->getControllerResult();
    $method = $event->getRequest()
                    ->getMethod();

    if ($result instanceof Customer && $method === "POST") {
      $user = $this->security->getUser(); // On récupère l'utilisateur grâce à la classe Security
      $result->setUser($user);
    }
  }

  public static function getSubscribedEvents() {
    return [
      KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_VALIDATE],
    ];
  }
}
