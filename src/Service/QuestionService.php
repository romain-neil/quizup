<?php
namespace App\Service;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

class QuestionService {

	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $manager;

	public function __construct(EntityManagerInterface $manager) {
		$this->manager = $manager;
	}

	public function inport(string $file, ProgressBar $bar, OutputInterface $output) {
		$fileType = IOFactory::identify($file);
		$reader = IOFactory::createReader($fileType);

		$feuille = $reader->load($file);
		$page = $feuille->getActiveSheet();

		//Pour chaque ligne, vérifier si elle est vide
		//Si c'est le cas, alors arrêter le script d'importation

		$i = 2;

		while(true) {
			if($page->getCell('A' . $i)->getFormattedValue() == "") {
				$output->writeln("<info>Fin de traitement du fichier</info>");
				$output->writeln("Nombre de questions importées : $i");

				break;
			}

			$question = new Question();
			$j = 0;

			//Si il y a une image
			if($page->getCell('B' . $i)->getFormattedValue() == "Image") {
				$question->setImage($page->getCell('A' . $i)->getFormattedValue());
			}

			$question->setLibele($page->getCell('C' . $i)->getFormattedValue());

			//Pour chaque réponses
			foreach (range('D', 'G') as $letter) {
				$reponse = new Answer();
				$reponse->setLibele($page->getCellByColumnAndRow(4 + $j, $i)->getFormattedValue());

				$reponse->setIsCorrect($page->getCellByColumnAndRow(8 + $j, $i)->getFormattedValue() == "1");

				$this->manager->persist($reponse);

				$question->addAnswer($reponse);

				$j++;
			}

			//Enregistrement des réponses
			$this->manager->persist($question);
			$this->manager->flush();

			//...
			$bar->advance();

			$i++;
		}

		$output->writeln("Nombre de questions importees : " . $i);
	}

}
