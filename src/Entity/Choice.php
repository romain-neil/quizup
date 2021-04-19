<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente un choix fait par un utilisateur en bdd
 * @ORM\Entity(repositoryClass=ChoiceRepository::class)
 * @ORM\Table(name="choices")
 */
class Choice {

	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * Question liée au choix
	 * @ORM\ManyToOne(targetEntity=Question::class)
	 */
	private ?Question $question;

	/**
	 * @ORM\ManyToMany(targetEntity=Answer::class)
	 * @ORM\JoinTable(name="selected_answers")
	 */
	private Collection $answers;

	/**
	 * @ORM\ManyToOne(targetEntity=Participation::class, inversedBy="choices")
	 */
	private ?Participation $participation;

	public function __construct() {
		$this->answers = new ArrayCollection();
	}

	public function getId(): ?int {
		return $this->id;
	}

	public function getQuestion(): ?Question {
		return $this->question;
	}

	public function setQuestion(?Question $question): self {
		$this->question = $question;

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
		}

		return $this;
	}

	public function removeAnswer(Answer $answer): self {
		$this->answers->removeElement($answer);

		return $this;
	}

	public function getParticipation(): ?Participation {
		return $this->participation;
	}

	public function setParticipation(?Participation $participation): self {
		$this->participation = $participation;

		return $this;
	}

}
