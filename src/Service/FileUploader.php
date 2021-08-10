<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader {

	private string $targetDirectory;
	private SluggerInterface $slugger;

	public function __construct(string $targetDirectory, SluggerInterface $slugger) {
		$this->targetDirectory = $targetDirectory;
		$this->slugger = $slugger;
	}

	public function upload(UploadedFile $file): ?string {
		$originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
		$safeFileName = $this->slugger->slug($originalFileName);
		$fileName = $safeFileName . '-' . uniqid() . '.jpg';

		try {
			$file->move($this->targetDirectory, $fileName);
		} catch(FileException $e) {
			return null;
		}

		return $fileName;
	}

	public function getTargetDirectory(): string {
		return $this->targetDirectory;
	}

}
