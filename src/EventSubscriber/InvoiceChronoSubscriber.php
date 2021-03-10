<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

class InvoiceChronoSubscriber implements EventSubscriberInterface {
  private $security;
  private $repository;

  public function __construct(Security $security, InvoiceRepository $repository) {
    $this->security   = $security;
    $this->repository = $repository;
  }

  public function onKernelView(ViewEvent $event) {
    $result = $event->getControllerResult();
    $method = $event->getRequest()
                    ->getMethod();
    $user   = $this->security->getUser(); // On récupère l'utilisateur grâce à la classe Security

    if ($result instanceof Invoice && $method === "POST") {
      $nextChrono = $this->repository->findNextChrono($user);
      $result->setChrono($nextChrono);
      if (!$result->getSentAt()) $result->setSentAt(new \DateTime()); // FIXME : À deplacer dans une autre classe
    }
  }

  public static function getSubscribedEvents() {
    return [
      KernelEvents::VIEW => ['onKernelView', EventPriorities::PRE_VALIDATE],
    ];
  }
}
