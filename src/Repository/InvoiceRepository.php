<?php

namespace App\Repository;

use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository {
  public function __construct(ManagerRegistry $registry) {
    parent::__construct($registry, Invoice::class);
  }

  /**
   * @param User $user
   *
   * @return string
   */
  public function findLastChrono(User $user): string {
    try {
      return $this->createQueryBuilder("i") // on fait une requête sur les invoices que l'on labellise à i
                  ->select("i.chrono") // on sélectionne uniquement le chrono
                  ->join("i.customer", "c") // on vient fusionner avec les clients
                  ->where("c.user = :user") // seulement si l'utilisateur est égale à la variable nommée user
                  ->setParameter("user", $user) // que l'on fournit ici
                  ->orderBy("i.chrono", "DESC") // on trie du plus grans au plus petit
                  ->setMaxResults(1) // on récupére un seul élément du résultat
                  ->getQuery() // On récupère la requête
                  ->getSingleScalarResult(); // et on retourne seulement un résultat (i.e. ici le numéro en valeur de chrono)
    }
    catch (NoResultException | NonUniqueResultException $e) {
      dd($e);
    }
  }

  public function findNextChrono(User $user): int {
    return (int) $this->findLastChrono($user) + 1;
  }

  // /**
  //  * @return Invoice[] Returns an array of Invoice objects
  //  */
  /*
  public function findByExampleField($value)
  {
      return $this->createQueryBuilder('i')
          ->andWhere('i.exampleField = :val')
          ->setParameter('val', $value)
          ->orderBy('i.id', 'ASC')
          ->setMaxResults(10)
          ->getQuery()
          ->getResult()
      ;
  }
  */

  /*
  public function findOneBySomeField($value): ?Invoice
  {
      return $this->createQueryBuilder('i')
          ->andWhere('i.exampleField = :val')
          ->setParameter('val', $value)
          ->getQuery()
          ->getOneOrNullResult()
      ;
  }
  */
}
