<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Mettre en place de la validation permet d'offusquer les retours SQL qui sont une failles de sécurité en soit si
 * exposés.
 */
use Symfony\Component\Validator\Constraints as Assert;

/**
 * L'annotation ApiResource permet de lier une entité à ApiPlatform, de l'exposer comme une ressource à "n'importe qui"
 * à travers une URL La propriété normalizationContext va nous permettre d'effectuer certaines actions lors du passage
 * d'une entité Doctrine/PHP à un simple tableau. (cf. https://api-platform.com/docs/core/serialization/)
 *
 *
 * Ici, l'étiquette/label personnalisée "customers_read" va nous permettre - par exemple - de définir les champs à
 * fournir lorsqu'il y aura une action de lecture sur notre entité. Si il n'y a qu'un groupe, il sera utilisé par
 * défaut.
 *
 * Attention : dans l'idéal, il est conseillé de toujours avoir des groupes.
 *
 *
 *
 * Il est possible d'activer/de désactiver les opérations de type collection (GET et POST de manière global) et les
 * opérations de type item (GET by id, PUT, PATCH et DELETE).
 * Cf. https://api-platform.com/docs/core/operations/
 *
 * Ici, on peut laissé activé les opérations de collections et d'items de la méthode GET par exemple.
 *
 *
 * Il est également possible de modifier les opérations de l'API (e.g. ici on change le nom du chemin).
 *
 *
 *
 * Il est possible de configurer une sous-ressource. Il faut tout d'abord rajouter la propriété subresourceOperations qui
 * est un tableau associatif (1). Il faut ensuite désigner notre sous-ressource, pour cela il y a une annotation spéciale
 * qui est propre à API Platform [nom de la sous-ressource/champ]_get_subresource (puisque les sous-ressources ne fonctionnent
 * actuellement qu'avec des opeérations de type GET).
 *
 *
 *
 *
 * @ApiResource(
 *   normalizationContext={
 *     "groups"={
 *       "customers_read"
 *     }
 *   },
 *   collectionOperations={"GET"={"path"="/clients"}, "POST"},
 *   itemOperations={"GET"={"path"="/client/{id}"}, "PUT", "PATCH", "DELETE"},
 *   subresourceOperations={
 *     "invoices_get_subresource"={
 *        "path"="/clients/{id}/factures"
 *      }
 *   }
 * )
 *
 *
 *
 * Le premier paramètre de ApiFilter est le type de filtre que l'on souhaite utiliser (ici la classe SearchFilter qui
 * nous est livrée avec ApiPlatform), le second paramètre (qui est optionnel) sont les propriétés que l'on souhaite
 * filtrer.
 *
 *
 *
 * ApiFilter va permettre à une requête de filtrer les propriétés. Il est possible de passer une stratégie de recherche
 * (exacte, partiel, début, fin et commençant par un mot spécifique) qui permet de définir l'algorithme de recherche à
 * l'image d'une regex.
 * (cf. https://api-platform.com/docs/core/filters/#search-filter)
 *
 *
 *
 * @ApiFilter(
 *   SearchFilter::class, properties={"firstname"="partial", "lastname", "company"}
 * )
 *
 *
 *
 * Il est possible de cumuler les filtres, en les faisant suivre les uns à la suite des autres.
 * Il est également possible de filtrer sur des propriétés liées par des relations en faisant suivre la propriété par
 * un point, à l'instar d'un objet Javascript. (cf.
 * https://api-platform.com/docs/core/filters/#filtering-on-nested-properties)
 *
 *
 *
 * @ApiFilter(
 *   OrderFilter::class,
 * )
 * @ORM\Entity(repositoryClass=CustomerRepository::class)
 */
class Customer {
  /**
   * @ORM\Id
   * @ORM\GeneratedValue
   * @ORM\Column(type="integer")
   * Dans le cas où on retrouverait un client dans une facture, pour afficher toutes les informations du clients dans
   * la même requête d'une facture, il faut le lier à l'un des groupes d'une facture (cf. Invoice Entity), i.e. à l'un
   * des contextes qui sera présent uniquement lorsque l'on "recevra" une facture au travers l'API.
   * @Groups({ "customers_read", "invoices_read" })
   */
  private $id;

  /**
   * @ORM\Column(type="string", length=255)
   * Nous associons le champ au groupe de lecture que nous avons créé plus haut. Et l'identifiant sera ressorti à la
   * lecture.
   * @Groups({ "customers_read", "invoices_read" })
   * @Assert\NotBlank(message="Le prénom du client est obligatoire")
   * @Assert\Length(min=3, minMessage="Le prénom doit faire entre 3 caractères et 255 caractères", max=255, maxMessage="Le prénom doit faire entre 3 caractères et 255 caractères")
   */
  private $firstname;

  /**
   * @ORM\Column(type="string", length=255)
   * @Groups({ "customers_read", "invoices_read" })
   * @Assert\NotBlank(message="Le nom du client est obligatoire")
   */
  private $lastname;

  /**
   * @ORM\Column(type="string", length=255)
   * @Groups({ "customers_read", "invoices_read" })
   * @Assert\NotBlank(message="L'email' du client est obligatoire")
   * @Assert\Email(message="Le format de l'adresse e-mail doit être valide")
   */
  private $email;

  /**
   * @ORM\Column(type="string", length=255, nullable=true)
   * @Groups({ "customers_read", "invoices_read" })
   */
  private $company;

  /**
   * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="customer")
   * @Groups({ "customers_read" })
   * Une sous-ressource permet de filtrer des informations. E.g. ici nous allons pouvois récupérer sur une route spécifique,
   * toutes les factures pour un client spécifique. (cf. https://api-platform.com/docs/core/subresources/)
   * @ApiSubresource()
   */
  private $invoices;

  /**
   * @ORM\ManyToOne(targetEntity=User::class, inversedBy="customers")
   * @Groups({ "customers_read", "invoices_read" })
   * Attention, ici il faut fournir un IRI (cf. API Platform)
   * @Assert\NotBlank(message="L'utilisateur est obligatoire")
   */
  private $user;

  public function __construct() {
    $this->invoices = new ArrayCollection();
  }

  public function getId(): ?int {
    return $this->id;
  }

  public function getFirstname(): ?string {
    return $this->firstname;
  }

  public function setFirstname(string $firstname): self {
    $this->firstname = $firstname;

    return $this;
  }

  public function getLastname(): ?string {
    return $this->lastname;
  }

  public function setLastname(string $lastname): self {
    $this->lastname = $lastname;

    return $this;
  }

  public function getEmail(): ?string {
    return $this->email;
  }

  public function setEmail(string $email): self {
    $this->email = $email;

    return $this;
  }

  public function getCompany(): ?string {
    return $this->company;
  }

  public function setCompany(?string $company): self {
    $this->company = $company;

    return $this;
  }

  /**
   * @return Collection|Invoice[]
   */
  public function getInvoices(): Collection {
    return $this->invoices;
  }

  public function addInvoice(Invoice $invoice): self {
    if (!$this->invoices->contains($invoice)) {
      $this->invoices[] = $invoice;
      $invoice->setCustomer($this);
    }

    return $this;
  }

  public function removeInvoice(Invoice $invoice): self {
    if ($this->invoices->removeElement($invoice)) {
      // set the owning side to null (unless already changed)
      if ($invoice->getCustomer() === $this) {
        $invoice->setCustomer(null);
      }
    }

    return $this;
  }

  /**
   * Fonction créée pour calculer un champ à la volée avant son rendu par l'API. Elle permet de récupérer le total des
   * invoices.
   *
   * Grâce à l'annotation Groups, on expose une nouvelle variable au travers du retour par l'API
   *
   * @Groups({"customers_read"})
   *
   * @return float
   */
  public function getTotalInvoiceAmount(): float {
    /**
     * 1) Ici, la variable invoices est une collection d'invoices que l'on va transformer en tableau (pour que le reducer fonctionne)
     * 2) On va ajouter au total (qui est initialisé à zéro) le prix de la facture
     */
    return array_reduce($this->invoices->toArray(), function($total, $invoice) {
      return $total + $invoice->getAmount();
    }, 0);
  }

  public function getUser(): ?User {
    return $this->user;
  }

  public function setUser(?User $user): self {
    $this->user = $user;

    return $this;
  }
}
