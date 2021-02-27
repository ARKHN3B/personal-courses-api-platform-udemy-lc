<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * La validation passe après une vérification de gestion des types par API Platform
 */
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Comme toutes les annotations, ApiResource est une fonction à laquelle on peut ajouter des paramètres.
 * Ici, on active
 *  - la pagination (pagination_enabled) ;
 *  - on rend 20 items par page (pagination_items_per_page) ;
 *  - on affiche d'abord les éléments avec le dernière date (order->sentAt) ;
 *
 * Il est possible de paramètrer le rendu des clients lorsque les factures sont appelées comme une sous-ressource (cf.
 * GET customer by a specific id).
 * Il y a une annotation assez particulière pour cette action qui est toujours de type GET :
 *            - "api_[nom de la ressource appelante]_[nom de la ressource appelée]_get_subresource"
 * Cela va permettre avec un contexte de normalisation de choisir quel champ on souhaite exposé lorsqu'il s'agit d'une
 * sous-ressource.
 *
 * Il est possible de passer des opérations personnalisées (comprenez ici de nouvelles routes) via le biai de la
 * propriété "itemOperation" en donnant une clé personnalisée, ici "increment"
 *
 *
 * La propriété denormalizationContext permet de configurer certaines actions lors de la dénormalisation. Ici, on indique
 * avec la clé "disable_type_enforcement" que l'on souhaite désactiver la vérification du type par API Platform lors de
 * l'enregistrement des données. Attention: il faut aussi enlever le typage dans les paramètres des setters.
 *
 * @ApiResource(
 *   subresourceOperations={
 *     "api_customers_invoices_get_subresource"={
 *        "normalization_context"={
 *          "groups"={"invoices_subresource"}
 *       }
 *     }
 *   },
 *   itemOperations={
 *   "GET", "PUT", "DELETE",
 *     "increment"={
 *       "method"="post",
 *       "path"="/invoices/{id}/increment",
 *       "controller"="App\Controller\InvoiceIncrementationController",
 *       "swagger_context"={
 *         "summary"="Incrémente une facture",
 *         "description"="Incrémente le chrono d'une facture donnée"
 *       }
 *     }
 *   },
 *  attributes={
 *     "pagination_enabled"=true,
 *     "pagination_items_per_page"=20,
 *     "order"={"sentAt"="desc"},
 *   },
 *  normalizationContext={
 *    "groups": { "invoices_read" }
 *   },
 *   denormalizationContext={
 *     "disable_type_enforcement"=true
 *   }
 * )
 *
 *
 *
 *
 * À l'instar du SearchFilter, on peut préciser les propriétés sur lesquelles on souhaite ouvrir l'ordre.
 *
 *
 *
 * @ApiFilter(
 *   OrderFilter::class
 * )
 *
 * @ORM\Entity(repositoryClass=InvoiceRepository::class)
 */
class Invoice
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     * @Assert\NotBlank(message="Le nontant de la facture est obligatoire")
     * @Assert\Type(type="numeric", message="Le montant de la facture doit être un numérique")
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     * @Assert\Type("\DateTimeInterface")
     * @Assert\NotBlank(message="La date d'envoi doit être renseignée")
     */
    private $sentAt;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     * @Assert\NotBlank(message="Le statut doit être renseigné")
     * @Assert\Choice(choices={"SENT", "PAID", "CANCELLED"}, message="Le statut doit étre au choix: SENT, PAID ou CANCELLED")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=Customer::class, inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"invoices_read"})
     * @Assert\NotBlank(message="Le client de la facture doit être renseigné")
     */
    private $customer;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"invoices_read", "customers_read", "invoices_subresource"})
     * @Assert\NotBlank(message="Le chrono de la facture doit être renseignée")
     */
    private $chrono;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

  /**
   * ATTENTION : on enlève le typage du paramètre ici pour pouvoir effectuer une vérification manuelle par le système de
   * validation de Symfony sans API Platform
   *
   * @param $amount
   *
   * @return $this
   */
    public function setAmount($amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getSentAt(): ?\DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTimeInterface $sentAt): self
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    public function getChrono(): ?int
    {
        return $this->chrono;
    }

    public function setChrono(int $chrono): self
    {
        $this->chrono = $chrono;

        return $this;
    }
}
