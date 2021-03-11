<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader {

	private string $targetDirectory;
	private SluggerInterface $slugger;
	private string $error;

	public function __construct(string $targetDirectory, SluggerInterface $slugger) {
		$this->targetDirectory = $targetDirectory;
		$this->slugger = $slugger;
	}

	public function upload(UploadedFile $file): string {
		$fileName = 'question-' . uniqid() . '.jpg';

		$file->move($this->targetDirectory, $fileName);

		return $fileName;
	}

	/**
	 * @return string
	 */
	public function getError(): string {
		return $this->error;
	}

	public function getTargetDirectory(): string {
		return $this->targetDirectory;
	}

}
