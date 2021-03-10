<?php


namespace App\Doctrine;


use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

/**
 * Étends les interfaces qui permettent d'effectuer de la logique quand on fait appelle à une collection ou un item.
 * (cf. https://api-platform.com/docs/core/data-providers/)
 *
 * Class CurrentUserExtension
 *
 * @package App\Doctrine
 */
class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface {

  private $security;
  private $auth;

  /**
   * CurrentUserExtension constructor.
   *
   * @param Security                      $security
   * @param AuthorizationCheckerInterface $authorizationChecker Cette interface nous permet de vérifier les autorisations des personnes
   */
  public function __construct(Security $security, AuthorizationCheckerInterface $authorizationChecker) {
    $this->security = $security;
    $this->auth = $authorizationChecker;
  }

  /**
   * Permet d'ajouter des correctifs ou améliorer la requête
   *
   * Par exemple, ici on ne fournit que les factures et les clients relatifs avec un utilisateur
   *
   * @param QueryBuilder                $queryBuilder  Fabrique de la requête (comprendre factory)
   * @param QueryNameGeneratorInterface $queryNameGenerator
   * @param string                      $resourceClass Le nom de la classe sur laquelle on envoie la requête
   * @param string|null                 $operationName
   */
  public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator,
                                    string $resourceClass, string $operationName = null) {
    $this->addWhere($queryBuilder, $resourceClass);
  }

  public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator,
                              string $resourceClass, array $identifiers, string $operationName = null,
                              array $context = []) {
    $this->addWhere($queryBuilder, $resourceClass);
  }

  private function addWhere(QueryBuilder $queryBuilder, string $resourceClass) {
    $user = $this->security->getUser();

    // Si l'utilisateur est présent (donc connecté) alors on fait pas cette logique
    if (($resourceClass === Customer::class || $resourceClass === Invoice::class) && !$this->auth->isGranted("ROLE_ADMIN") && $user instanceof User) {
      $rootAlias = $queryBuilder->getRootAliases()[0]; // On récupère le premier alias d'une requête SQL (SELECT * FROM ... AS "o" ...)

      if ($resourceClass === Customer::class) {
        $queryBuilder->andWhere("$rootAlias.user = :user");
      }
      else if ($resourceClass === Invoice::class) {
        $queryBuilder->join("$rootAlias.customer", "c")->andWhere("c.user = :user");
      }

      $queryBuilder->setParameter("user", $user);
    }
  }
}