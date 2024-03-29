<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use JMS\Serializer\Annotation\Groups;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @Hateoas\Relation(
 *      name = "create",
 *      href = @Hateoas\Route(
 *          "user_create",
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"user:list"}),
 *      attributes = {"method": "POST" }
 * )
 * @Hateoas\Relation(
 *      name = "self",
 *      href = @Hateoas\Route(
 *          "user_show",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"user:list", "user:show"}),
 *      attributes = {"method": "GET" }
 * )
 * @Hateoas\Relation(
 *      name = "delete",
 *      href = @Hateoas\Route(
 *          "user_delete",
 *          parameters = { "id" = "expr(object.getId())" },
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"user:list", "user:show"}),
 *      attributes = {"method": "DELETE" }
 * )
 * @Hateoas\Relation(
 *      name = "all",
 *      href = @Hateoas\Route(
 *          "user_list",
 *          absolute = true
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups = {"user:show"}),
 *      attributes = {"methods": "GET" }
 * )
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(
    fields: ['email'],
    message: 'Please change your email because: {{ value }} is not available'
)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user:list', 'user:show'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['user:create', 'user:list', 'user:show'])]
    #[Assert\NotBlank(
        message: 'Can not be blank'
    )]
    #[Assert\Length(
        min: 2,
        minMessage: 'Must contain minimum {{ limit }} characters'
    )]
    private $firstname;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['user:create', 'user:list', 'user:show'])]
    #[Assert\NotBlank(
        message: 'Can not be blank'
    )]
    #[Assert\Length(
        min: 2,
        minMessage: 'Must contain minimum {{ limit }} characters'
    )]
    private $lastname;

    #[ORM\Column(type: 'string', length: 180)]
    #[Groups(['user:create', 'user:show'])]
    #[Assert\NotBlank(
        message: 'Can not be blank'
    )]
    #[Assert\Email(
        message: '{{ value }} is not a valid email address',
    )]
    private $email;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'users')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['user:show'])]
    private $customer;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(['user:show'])]
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
