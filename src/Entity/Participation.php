<?php
namespace App\Entity;

use App\Repository\ParticipationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ParticipationRepository::class)
 */
class Participation {

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\OneToMany(targetEntity=Choice::class, mappedBy="participation")
	 */
	private Collection $choices;

	/**
	 * @ORM\OneToOne(targetEntity=User::class, inversedBy="participation", cascade={"persist", "remove"})
	 */
	private ?User $utilisateur;

	/**
	 * @ORM\Column(type="integer", nullable=true)
	 */
	private ?int $points;

	public function __construct() {
		$this->choices = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	/**
	 * @return Collection|Choice[]
	 */
	public function getChoices(): Collection {
		return $this->choices;
	}

	public function addChoice(Choice $choice): self {
		if (!$this->choices->contains($choice)) {
			$this->choices[] = $choice;
			$choice->setParticipation($this);
		}

		return $this;
	}

	public function removeChoice(Choice $choice): self {
		if ($this->choices->removeElement($choice)) {
			// set the owning side to null (unless already changed)
			if ($choice->getParticipation() === $this) {
				$choice->setParticipation(null);
			}
		}

		return $this;
	}

	public function getUtilisateur(): ?User {
		return $this->utilisateur;
	}

	public function setUtilisateur(?User $utilisateur): self {
		$this->utilisateur = $utilisateur;

		return $this;
	}

	public function getPoints(): ?int {
		return $this->points;
	}

	public function setPoints(?int $points): self {
		$this->points = $points;

		return $this;
	}

}
