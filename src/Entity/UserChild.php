<?php

namespace App\Entity;

use App\Repository\UserChildRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserChildRepository::class)]
class UserChild
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $relation = null;

    #[ORM\ManyToOne(inversedBy: 'userChildren')]
    private ?Child $child = null;

    #[ORM\ManyToOne(inversedBy: 'userChildren')]
    private ?User $user = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRelation(): ?string
    {
        return $this->relation;
    }

    public function setRelation(string $relation): static
    {
        $this->relation = $relation;

        return $this;
    }

    public function getChild(): ?Child
    {
        return $this->child;
    }

    public function setChild(?Child $child): static
    {
        $this->child = $child;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

}
