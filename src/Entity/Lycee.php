<?php

namespace App\Entity;

use App\Repository\LyceeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente un lycée en base de donnée
 * @ORM\Entity(repositoryClass=LyceeRepository::class)
 */
class Lycee {

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $type;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $nom;

	/**
	 * @ORM\OneToMany(targetEntity=Classe::class, mappedBy="lycee")
	 */
	private Collection $classes;

	public function __construct() {
		$this->classes = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getType(): ?string {
		return $this->type;
	}

	public function setType(string $type): self {
		$this->type = $type;

		return $this;
	}

	public function getNom(): ?string {
		return $this->nom;
	}

	public function setNom(string $nom): self {
		$this->nom = $nom;

		return $this;
	}

	/**
	 * @return Collection|Classe[]
	 */
	public function getClasses(): Collection {
		return $this->classes;
	}

	public function addClass(Classe $class): self {
		if (!$this->classes->contains($class)) {
			$this->classes[] = $class;
			$class->setLycee($this);
		}

		return $this;
	}

	public function removeClass(Classe $class): self {
		if ($this->classes->removeElement($class)) {
			// set the owning side to null (unless already changed)
			if ($class->getLycee() === $this) {
				$class->setLycee(null);
			}
		}

		return $this;
	}
}
