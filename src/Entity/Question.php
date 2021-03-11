<?php

namespace App\Entity;

use App\Repository\QuestionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=QuestionRepository::class)
 */
class Question {

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
	 * @ORM\OneToMany(targetEntity=Answer::class, cascade={"persist", "remove"}, mappedBy="question")
	 */
	private Collection $answers;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 */
	private ?string $image;

	public function __construct() {
		$this->answers = new ArrayCollection();
	}

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
	 * @return Collection|Answer[]
	 */
	public function getAnswers(): Collection {
		return $this->answers;
	}

	public function addAnswer(Answer $answer): self {
		if (!$this->answers->contains($answer)) {
			$this->answers[] = $answer;
			$answer->setQuestion($this);
		}

		return $this;
	}

	public function removeAnswer(Answer $answer): self {
		if ($this->answers->removeElement($answer)) {
			// set the owning side to null (unless already changed)
			if ($answer->getQuestion() === $this) {
				$answer->setQuestion(null);
			}
		}

		return $this;
	}

	public function getImage(): ?string {
		return $this->image;
	}

	public function setImage(?string $image): self {
		$this->image = $image;

		return $this;
	}

	/**
	 * Supprime les réponses
	 */
	public function flushAnswers() {
		$this->answers->clear();
	}

}
