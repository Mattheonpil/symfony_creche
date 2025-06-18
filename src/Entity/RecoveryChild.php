<?php

namespace App\Entity;

use App\Repository\RecoveryChildRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecoveryChildRepository::class)]
class RecoveryChild
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $relation = null;

    #[ORM\Column]
    private ?bool $is_responsable = null;

    #[ORM\ManyToOne(inversedBy: 'recoveryChildren')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Child $child = null;

    #[ORM\ManyToOne(inversedBy: 'recoveryChildren')]
    private ?Recovery $recovery = null;

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

    public function isResponsable(): ?bool
    {
        return $this->is_responsable;
    }

    public function setIsResponsable(bool $is_responsable): static
    {
        $this->is_responsable = $is_responsable;

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

    public function getRecovery(): ?Recovery
    {
        return $this->recovery;
    }

    public function setRecovery(?Recovery $recovery): static
    {
        $this->recovery = $recovery;

        return $this;
    }
}
