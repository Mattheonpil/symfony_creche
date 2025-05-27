<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $first_name = null;

    #[ORM\Column(length: 50)]
    private ?string $mail = null;

    #[ORM\Column(length: 10)]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    /**
     * @var Collection<int, UserChild>
     */
    #[ORM\OneToMany(targetEntity: UserChild::class, mappedBy: 'user')]
    private Collection $userChildren;

    public function __construct()
    {
        $this->userChildren = new ArrayCollection();
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

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): static
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getMail(): ?string
    {
        return $this->mail;
    }

    public function setMail(string $mail): static
    {
        $this->mail = $mail;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Collection<int, UserChild>
     */
    public function getUserChildren(): Collection
    {
        return $this->userChildren;
    }

    public function addUserChild(UserChild $userChild): static
    {
        if (!$this->userChildren->contains($userChild)) {
            $this->userChildren->add($userChild);
            $userChild->setUser($this);
        }

        return $this;
    }

    public function removeUserChild(UserChild $userChild): static
    {
        if ($this->userChildren->removeElement($userChild)) {
            // set the owning side to null (unless already changed)
            if ($userChild->getUser() === $this) {
                $userChild->setUser(null);
            }
        }

        return $this;
    }
}
