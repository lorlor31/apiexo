<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[Groups(['order'])]

class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['orderLinked'])]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['orderLinked'])]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    #[Groups(['orderLinked'])]
    private ?bool $delivered = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['orderLinked'])]
    private ?\DateTimeInterface $delivery_order = null;

    #[ORM\ManyToMany(targetEntity: Product::class, inversedBy: 'y')]
    private Collection $products;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customer $customer = null;

    public function __construct()
    {
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function isDelivered(): ?bool
    {
        return $this->delivered;
    }

    public function setDelivered(bool $delivered): static
    {
        $this->delivered = $delivered;

        return $this;
    }

    public function getDeliveryOrder(): ?\DateTimeInterface
    {
        return $this->delivery_order;
    }

    public function setDeliveryOrder(?\DateTimeInterface $delivery_order): static
    {
        $this->delivery_order = $delivery_order;

        return $this;
    }

    /**
     * @return Collection<int, Product>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
        }

        return $this;
    }

    public function removeProduct(Product $product): static
    {
        $this->products->removeElement($product);

        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): static
    {
        $this->customer = $customer;

        return $this;
    }
}
