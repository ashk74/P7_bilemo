<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use App\Repository\ProductRepository;
use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *     name = "self",
 *     href = @Hateoas\Route(
 *         "product_show",
 *         parameters = { "id" = "expr(object.getId())" },
 *         absolute = true
 *     ),
 *      exclusion = @Hateoas\Exclusion(groups = {"product:list", "product:show"}),
 *      attributes = {"methods": "GET" }
 * )
 * @Hateoas\Relation(
 *     name = "all",
 *     href = @Hateoas\Route(
 *         "product_list",
 *         absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"product:show"}),
 *      attributes = {"methods": "GET" }
 * )
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['product:list', 'product:show'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['product:list', 'product:show'])]
    private $name;

    #[ORM\Column(type: 'text')]
    #[Groups(['product:show'])]
    private $description;

    #[ORM\Column(type: 'integer')]
    #[Groups(['product:show'])]
    private $quantity;

    #[ORM\Column(type: 'decimal', precision: 6, scale: 2)]
    #[Groups(['product:list', 'product:show'])]
    private $price;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['product:show'])]
    private $publishedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getPublishedAt(): ?\DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTimeImmutable $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }
}
