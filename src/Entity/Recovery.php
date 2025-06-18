<?php

namespace App\Entity;

use App\Repository\RecoveryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RecoveryRepository::class)]
class Recovery
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $first_name = null;

    #[ORM\Column(length: 10)]
    private ?string $phone = null;

    /**
     * @var Collection<int, RecoveryChild>
     */
    #[ORM\OneToMany(targetEntity: RecoveryChild::class, mappedBy: 'recovery')]
    private Collection $recoveryChildren;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $email = null;



    public function __construct()
    {
        $this->child = new ArrayCollection();
        $this->recoveryChildren = new ArrayCollection();
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return Collection<int, Child>
     */
    public function getChild(): Collection
    {
        return $this->child;
    }

    public function addChild(Child $child): static
    {
        if (!$this->child->contains($child)) {
            $this->child->add($child);
        }

        return $this;
    }

    public function removeChild(Child $child): static
    {
        $this->child->removeElement($child);

        return $this;
    }

    /**
     * @return Collection<int, RecoveryChild>
     */
    public function getRecoveryChildren(): Collection
    {
        return $this->recoveryChildren;
    }

    public function addRecoveryChild(RecoveryChild $recoveryChild): static
    {
        if (!$this->recoveryChildren->contains($recoveryChild)) {
            $this->recoveryChildren->add($recoveryChild);
            $recoveryChild->setRecovery($this);
        }

        return $this;
    }

    public function removeRecoveryChild(RecoveryChild $recoveryChild): static
    {
        if ($this->recoveryChildren->removeElement($recoveryChild)) {
            // set the owning side to null (unless already changed)
            if ($recoveryChild->getRecovery() === $this) {
                $recoveryChild->setRecovery(null);
            }
        }

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }
}
