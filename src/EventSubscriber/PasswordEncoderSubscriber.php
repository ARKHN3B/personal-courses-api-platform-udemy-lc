<?php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PasswordEncoderSubscriber implements EventSubscriberInterface {

  private $encoder;

  public function __construct(UserPasswordEncoderInterface $encoder) {
    $this->encoder = $encoder;
  }

  public function encodePassword(ViewEvent $event) {
    /** La méthode getControllerResult va permettre de récupérer le résultat déserialisé du controleur d'ApiPlatform.
     *
     * Le contrôleur d'ApiPlatform va obtenir le JSON reçu par la requête HTTP, le transformer en le désérialisant en
     * une entité et retourner cette entité.
     *
     * Un autre composant d'ApiPLatform se charge ensuite de l'enregistrer en base de données.
     *
     * Dans cet événement, nous nous trouvons entre ces deux moments/composants.
     */
    $result = $event->getControllerResult();
    $method = $event->getRequest()
                    ->getMethod();

    if ($result instanceof User && $method === "POST") {
      $hash = $this->encoder->encodePassword($result, $result->getPassword());
      $result->setPassword($hash);
    }
  }

  public static function getSubscribedEvents() {
    /**
     * https://api-platform.com/docs/core/events/#built-in-event-listeners
     * L'événement kernel.view se passe après la désérialisation.
     *
     * Du coup, on place notre méthode selon un ordre de prioriété
     */
    return [
      // Sur l'évènement VIEW, on appelle la fonction encodePassword avant l'écriture (PRE_WRITE)
      KernelEvents::VIEW => ["encodePassword", EventPriorities::PRE_WRITE],
    ];
  }
}
