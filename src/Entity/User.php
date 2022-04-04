<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['user:list', 'user:show'])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['user:list', 'user:show'])]
    #[Assert\NotBlank(
        message: 'Can not be blank'
    )]
    #[Assert\Length(
        min: 2,
        minMessage: 'Must contain minimum {{ limit }} characters'
    )]
    private $firstname;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['user:list', 'user:show'])]
    #[Assert\NotBlank(
        message: 'Can not be blank'
    )]
    #[Assert\Length(
        min: 2,
        minMessage: 'Must contain minimum {{ limit }} characters'
    )]
    private $lastname;

    #[ORM\Column(type: 'string', length: 180)]
    #[Groups(['user:show'])]
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
