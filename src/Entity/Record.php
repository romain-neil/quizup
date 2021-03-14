<?php

namespace App\Entity;

use App\Repository\RecordRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RecordRepository::class)
 */
class Record {
	/**
	 * @ORM\Id
	 * @ORM\GeneratedValue
	 * @ORM\Column(type="integer")
	 */
	private ?int $id;

	/**
	 * @ORM\Column(type="integer")
	 */
	private ?int $duration;

	/**
	 * @ORM\Column(type="integer")
	 */
	private ?int $participation_id;

	public function getId(): ?int {
		return $this->id;
	}

	public function getDuration(): ?int {
		return $this->duration;
	}

	public function setDuration(int $duration): self {
		$this->duration = $duration;

		return $this;
	}

	public function getParticipationId(): ?int {
		return $this->participation_id;
	}

	public function setParticipationId(int $participation_id): self {
		$this->participation_id = $participation_id;

		return $this;
	}
}
