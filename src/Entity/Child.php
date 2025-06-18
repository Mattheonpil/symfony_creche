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



    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $lundi_a = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $lundi_d = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $mardi_a = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $mardi_d = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $mercredi_a = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $mercredi_d = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $jeudi_a = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $jeudi_d = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $vendredi_a = null;

    #[ORM\Column(type: Types::TIME_MUTABLE, nullable: true)]
    private ?\DateTime $vendredi_d = null;

    #[ORM\Column(length: 40)]
    private ?string $genre = null;

    /**
     * @var Collection<int, RecoveryChild>
     */
    #[ORM\OneToMany(targetEntity: RecoveryChild::class, mappedBy: 'child')]
    private Collection $recoveryChildren;

    public function __construct()
    {
        $this->userChildren = new ArrayCollection();
        $this->plannings = new ArrayCollection();
        $this->recoveries = new ArrayCollection();
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

    public function getLundiA(): ?\DateTime
    {
        return $this->lundi_a;
    }

    public function setLundiA(?\DateTime $lundi_a): static
    {
        $this->lundi_a = $lundi_a;

        return $this;
    }

    public function getLundiD(): ?\DateTime
    {
        return $this->lundi_d;
    }

    public function setLundiD(?\DateTime $lundi_d): static
    {
        $this->lundi_d = $lundi_d;

        return $this;
    }

    public function getMardiA(): ?\DateTime
    {
        return $this->mardi_a;
    }

    public function setMardiA(?\DateTime $mardi_a): static
    {
        $this->mardi_a = $mardi_a;

        return $this;
    }

    public function getMardiD(): ?\DateTime
    {
        return $this->mardi_d;
    }

    public function setMardiD(?\DateTime $mardi_d): static
    {
        $this->mardi_d = $mardi_d;

        return $this;
    }

    public function getMercrediA(): ?\DateTime
    {
        return $this->mercredi_a;
    }

    public function setMercrediA(?\DateTime $mercredi_a): static
    {
        $this->mercredi_a = $mercredi_a;

        return $this;
    }

    public function getMercrediD(): ?\DateTime
    {
        return $this->mercredi_d;
    }

    public function setMercrediD(?\DateTime $mercredi_d): static
    {
        $this->mercredi_d = $mercredi_d;

        return $this;
    }

    public function getJeudiA(): ?\DateTime
    {
        return $this->jeudi_a;
    }

    public function setJeudiA(?\DateTime $jeudi_a): static
    {
        $this->jeudi_a = $jeudi_a;

        return $this;
    }

    public function getJeudiD(): ?\DateTime
    {
        return $this->jeudi_d;
    }

    public function setJeudiD(?\DateTime $jeudi_d): static
    {
        $this->jeudi_d = $jeudi_d;

        return $this;
    }

    public function getVendrediA(): ?\DateTime
    {
        return $this->vendredi_a;
    }

    public function setVendrediA(?\DateTime $vendredi_a): static
    {
        $this->vendredi_a = $vendredi_a;

        return $this;
    }

    public function getVendrediD(): ?\DateTime
    {
        return $this->vendredi_d;
    }

    public function setVendrediD(?\DateTime $vendredi_d): static
    {
        $this->vendredi_d = $vendredi_d;

        return $this;
    }

    public function getGenre(): ?string
    {
        return $this->genre;
    }

    public function setGenre(string $genre): static
    {
        $this->genre = $genre;

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
            $recoveryChild->setChild($this);
        }

        return $this;
    }

    public function removeRecoveryChild(RecoveryChild $recoveryChild): static
    {
        if ($this->recoveryChildren->removeElement($recoveryChild)) {
            // set the owning side to null (unless already changed)
            if ($recoveryChild->getChild() === $this) {
                $recoveryChild->setChild(null);
            }
        }

        return $this;
    }
}
