<?php
namespace App\Service;

use App\Entity\Answer;
use App\Entity\Question;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use Symfony\Component\Console\Helper\ProgressBar;

class QuestionService {

	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $manager;

	public function __construct(EntityManagerInterface $manager) {
		$this->manager = $manager;
	}

	public function inport(string $file, ProgressBar $bar) {
		$reader = new Xls();

		$feuille = $reader->load($file);
		$feuille->setActiveSheetIndex(0);
		$page = $feuille->getActiveSheet();

		//Pour chaque ligne, vérifier si elle est vide
		//Si c'est le cas, alors arrêter le script d'importation

		$n = 400;
		for($i = 2; $i < $n; $i++) {
			if($page->getCell('A' . $i)->getFormattedValue() == "") {
				return;
			}

			$question = new Question();
			$answers = [];
			$j = 0;

			//Si il y a une image

			$question->setLibele($page->getCell('C' . $i)->getFormattedValue());

			//Pour chaque réponses
			foreach (range('D', 'G') as $letter) {
				$reponse = new Answer();
				$reponse->setLibele($page->getCell($letter . $i)->getFormattedValue());

				$reponse->setIsCorrect($page->getCellByColumnAndRow(8 + $j, $n)->getFormattedValue() == "1");

				$question->addAnswer($reponse);

				$this->manager->persist($reponse);

				$j++;
			}

			//Enregistrement des réponses
			$this->manager->persist($question);

			//...
			$bar->advance();
		}

		$this->manager->flush();
	}

}
