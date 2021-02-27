<?php

namespace App\Controller;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InvoiceIncrementationController extends AbstractController {

  /**
   * @var EntityManagerInterface
   */
  private $manager;

  /**
   * InvoiceIncrementationController constructor.
   *
   * @param EntityManagerInterface $manager
   */
  public function __construct(EntityManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * La fonction __invoke est une méthode spécifique liée à PHP qui est exécutée lorsque la classe est appelée en tant
   * que fonction, i.e. sans fournir d'autres méthodes à l'intérieur.
   *
   * @param Invoice $data - Cette argument doit VRAIMENT s'appeler $data par convention
   */
  public function __invoke(Invoice $data) {
    $data->setChrono($data->getChrono() + 1);

    // Pas besoin de persister les données ici puisqu'elles sont déjà fournises par le manager
    $this->manager->flush();

    return $data; // On retourne les données qui seront affichées sous la forme d'un JSON
  }
}
