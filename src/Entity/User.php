<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface {

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\Column(type="string", length=180, unique=true)
	 */
	private ?string $uuid;

	/**
	 * @ORM\Column(type="json")
	 */
	private array $roles = [];

	/**
	 * @var string The hashed password
	 * @ORM\Column(type="string")
	 */
	private string $password;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $prenom;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $nom;

	/**
	 * @ORM\OneToOne(targetEntity=Participation::class, mappedBy="utilisateur", cascade={"persist", "remove"})
	 */
	private ?Participation $participation;

	/**
	 * @ORM\ManyToOne(targetEntity=Classe::class, inversedBy="users")
	 */
	private ?Classe $classe;

	public function __construct() {}

	public function getId(): ?int {
		return $this->id;
	}

	public function getUuid(): ?string {
		return $this->uuid;
	}

	public function setUuid(string $uuid): self {
		$this->uuid = $uuid;

		return $this;
	}

	/**
	 * A visual identifier that represents this user.
	 *
	 * @see UserInterface
	 */
	public function getUsername(): string {
		return $this->getFancyName();
	}

	/**
	 * @see UserInterface
	 */
	public function getRoles(): array {
		$roles = $this->roles;
		// guarantee every user at least has ROLE_USER
		$roles[] = 'ROLE_USER';

		return array_unique($roles);
	}

	public function setRoles(array $roles): self {
		$this->roles = $roles;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function getPassword(): string {
		return (string)$this->password;
	}

	public function setPassword(string $password): self {
		$this->password = $password;

		return $this;
	}

	/**
	 * @see UserInterface
	 */
	public function getSalt() {
		// not needed when using the "bcrypt" algorithm in security.yaml
	}

	/**
	 * @see UserInterface
	 */
	public function eraseCredentials() {
		// If you store any temporary, sensitive data on the user, clear it here
		// $this->plainPassword = null;
	}

	public function getPrenom(): ?string {
		return $this->prenom;
	}

	public function setPrenom(string $prenom): self {
		$this->prenom = $prenom;

		return $this;
	}

	public function getNom(): ?string {
		return $this->nom;
	}

	public function setNom(string $nom): self {
		$this->nom = $nom;

		return $this;
	}

	public function getParticipation(): ?Participation {
		return $this->participation;
	}

	public function setParticipation(?Participation $participation): self {
		// unset the owning side of the relation if necessary
		if ($participation === null && $this->participation !== null) {
			$this->participation->setUtilisateur(null);
		}

		// set the owning side of the relation if necessary
		if ($participation !== null && $participation->getUtilisateur() !== $this) {
			$participation->setUtilisateur($this);
		}

		$this->participation = $participation;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFancyName(): string {
		return "{$this->prenom} {$this->nom}";
	}

	public function getClasse(): ?Classe {
		return $this->classe;
	}

	public function setClasse(?Classe $classe): self {
		$this->classe = $classe;

		return $this;
	}

}
