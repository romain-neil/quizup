<?php
namespace App\Entity;

use App\Repository\AnswerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AnswerRepository::class)
 */
class Answer {

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private ?string $libele;

	/**
	 * @ORM\Column(type="boolean")
	 */
	private ?bool $is_correct;

	/**
	 * @ORM\ManyToOne(targetEntity=Question::class, inversedBy="answers")
	 */
	private ?Question $question;

	public function getId(): ?int {
		return $this->id;
	}

	public function getLibele(): ?string {
		return $this->libele;
	}

	public function setLibele(string $libele): self {
		$this->libele = $libele;

		return $this;
	}

	/**
	 * La question est-elle correcte ?
	 * @return bool|null vrai si la question est correcte, faux sinon
	 */
	public function getIsCorrect(): ?bool {
		return $this->is_correct;
	}

	public function setIsCorrect(bool $is_correct): self {
		$this->is_correct = $is_correct;

		return $this;
	}

	public function getQuestion(): ?Question {
		return $this->question;
	}

	public function setQuestion(?Question $question): self {
		$this->question = $question;

		return $this;
	}

}
