<?php

namespace App\Entity;

use App\Repository\ChildRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChildRepository::class)]
class Child
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    private ?string $first_name = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $birth_date = null;

    #[ORM\Column]
    private ?\DateTime $registration_date = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $unsubscription_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $allergy = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $medical_specificity = null;

    /**
     * @var Collection<int, UserChild>
     */
    #[ORM\OneToMany(targetEntity: UserChild::class, mappedBy: 'child')]
    private Collection $userChildren;

    /**
     * @var Collection<int, Planning>
     */
    #[ORM\OneToMany(targetEntity: Planning::class, mappedBy: 'child')]
    private Collection $plannings;

    /**
     * @var Collection<int, Recovery>
     */
    #[ORM\ManyToMany(targetEntity: Recovery::class, mappedBy: 'child')]
    private Collection $recoveries;

    public function __construct()
    {
        $this->userChildren = new ArrayCollection();
        $this->plannings = new ArrayCollection();
        $this->recoveries = new ArrayCollection();
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

    public function getBirthDate(): ?\DateTime
    {
        return $this->birth_date;
    }

    public function setBirthDate(\DateTime $birth_date): static
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    public function getRegistrationDate(): ?\DateTime
    {
        return $this->registration_date;
    }

    public function setRegistrationDate(\DateTime $registration_date): static
    {
        $this->registration_date = $registration_date;

        return $this;
    }

    public function getUnsubscriptionDate(): ?\DateTime
    {
        return $this->unsubscription_date;
    }

    public function setUnsubscriptionDate(?\DateTime $unsubscription_date): static
    {
        $this->unsubscription_date = $unsubscription_date;

        return $this;
    }

    public function getAllergy(): ?string
    {
        return $this->allergy;
    }

    public function setAllergy(?string $allergy): static
    {
        $this->allergy = $allergy;

        return $this;
    }

    public function getMedicalSpecificity(): ?string
    {
        return $this->medical_specificity;
    }

    public function setMedicalSpecificity(?string $medical_specificity): static
    {
        $this->medical_specificity = $medical_specificity;

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
            $userChild->setChild($this);
        }

        return $this;
    }

    public function removeUserChild(UserChild $userChild): static
    {
        if ($this->userChildren->removeElement($userChild)) {
            // set the owning side to null (unless already changed)
            if ($userChild->getChild() === $this) {
                $userChild->setChild(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Planning>
     */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): static
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings->add($planning);
            $planning->setChild($this);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): static
    {
        if ($this->plannings->removeElement($planning)) {
            // set the owning side to null (unless already changed)
            if ($planning->getChild() === $this) {
                $planning->setChild(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Recovery>
     */
    public function getRecoveries(): Collection
    {
        return $this->recoveries;
    }

    public function addRecovery(Recovery $recovery): static
    {
        if (!$this->recoveries->contains($recovery)) {
            $this->recoveries->add($recovery);
            $recovery->addChild($this);
        }

        return $this;
    }

    public function removeRecovery(Recovery $recovery): static
    {
        if ($this->recoveries->removeElement($recovery)) {
            $recovery->removeChild($this);
        }

        return $this;
    }
}
