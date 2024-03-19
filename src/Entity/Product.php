<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[Groups(['product'])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['productLinked'])]
    private ?int $id = null;

    #[Groups(['productLinked'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['productLinked'])]
    #[ORM\Column]
    private ?float $price = null;

    #[Groups(['productLinked'])]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $color = null;

    #[Groups(['productLinked'])]
    #[ORM\Column]
    private ?bool $available = null;

    #[ORM\ManyToMany(targetEntity: Order::class, mappedBy: 'products')]
    private Collection $y;

    public function __construct()
    {
        $this->y = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): static
    {
        $this->available = $available;

        return $this;
    }

    /**
     * @return Collection<int, Order>
     */
    public function getY(): Collection
    {
        return $this->y;
    }

    public function addY(Order $y): static
    {
        if (!$this->y->contains($y)) {
            $this->y->add($y);
            $y->addProduct($this);
        }

        return $this;
    }

    public function removeY(Order $y): static
    {
        if ($this->y->removeElement($y)) {
            $y->removeProduct($this);
        }

        return $this;
    }
}
