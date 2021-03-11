<?php

namespace App\Entity;

use App\Repository\ClasseRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClasseRepository::class)
 */
class Classe {

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $nom;

	/**
	 * @ORM\ManyToOne(targetEntity=Lycee::class, inversedBy="classes")
	 */
	private ?Lycee $lycee;

	/**
	 * @ORM\OneToMany(targetEntity=User::class, mappedBy="classe")
	 */
	private Collection $users;

	public function __construct() {
		$this->users = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getNom(): ?string {
		return $this->nom;
	}

	public function setNom(string $nom): self {
		$this->nom = $nom;

		return $this;
	}

	public function getLycee(): ?Lycee {
		return $this->lycee;
	}

	public function setLycee(?Lycee $lycee): self {
		$this->lycee = $lycee;

		return $this;
	}

	/**
	 * @return Collection|User[]
	 */
	public function getUsers(): Collection {
		return $this->users;
	}

	public function addUser(User $user): self {
		if (!$this->users->contains($user)) {
			$this->users[] = $user;
			$user->setClasse($this);
		}

		return $this;
	}

	public function removeUser(User $user): self {
		if ($this->users->removeElement($user)) {
			// set the owning side to null (unless already changed)
			if ($user->getClasse() === $this) {
				$user->setClasse(null);
			}
		}

		return $this;
	}

}
