<?php


namespace App\EventListener;


use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener {
  /**
   * @var RequestStack
   */
  private $requestStack;

  /**
   * @param RequestStack $requestStack
   */
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * Cf. https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/2-data-customization.md pour
   * ajouter des informations sur le token (cf. sur https://jwt.io/).
   * @param JWTCreatedEvent $event
   *
   * @return void
   */
  public function onJWTCreated(JWTCreatedEvent $event) {
    $user = $event->getUser();
    $data = $event->getData();
    $data["firstname"] = $user->getFirstname(); // L'autocomplétion ne fonctionne pas mais les champs sont bien présents
    $data["lastname"] = $user->getLastname();

    $event->setData($data);
  }
}