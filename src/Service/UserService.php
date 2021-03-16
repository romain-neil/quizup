<?php
namespace App\Service;

use App\Entity\Classe;
use App\Entity\Lycee;
use App\Entity\Participation;
use App\Entity\Record;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Uid\Uuid;

class UserService {

	/**
	 * @var EntityManagerInterface
	 */
	private EntityManagerInterface $manager;

	private array $etlb;

	public function __construct(EntityManagerInterface $manager) {
		$this->manager = $manager;
	}

	public function import(string $file, ProgressBar $pBar) {
		$reader = new Xls();

		$spreedsheet = $reader->load($file);
		$spreedsheet->setActiveSheetIndex(0);

		$index = 0;

		while(true) { //Pour chaque ligne
			$page = $spreedsheet->getActiveSheet();

			if($page->getCell('A' . $index)->getFormattedValue() == "") {
				break;
			}

			$user = new User();

			$nomPrenom = $page->getCell("B" . $index)->getFormattedValue(); //DOE SPENCER John
			$pass = $page->getCell("C" . $index)->getCalculatedValue();
			$classe = $page->getCell("D" . $index)->getFormattedValue();

			//trie du prénom et du nom
			$nomPrenom = explode(" ", $nomPrenom);

			$c = count($nomPrenom);
			$c--;

			$prenom = $nomPrenom[$c]; //Dernier element du tableau
			$nom = [];

			for($j = 0; $j < $c; $j++) {
				$nom[] = $nomPrenom[$j];
			}

			//Récupération de l'établissement scolaire
			$typeEPLE = $page->getCell("E" . $index)->getValue();
			$nomEPLE = $page->getCell("F" . $index)->getValue();

			/** @var Lycee $eple */
			$eple = $this->manager->getRepository(Lycee::class)->findOneBy(['type' => $typeEPLE, 'nom' => $nomEPLE]);

			if($eple == null) {
				$etablissement = new Lycee();
				$etablissement->setNom($nomEPLE);
				$etablissement->setType($typeEPLE);

				$this->manager->persist($etablissement);
				$this->manager->flush();

				$this->etlb[$typeEPLE][$nomEPLE]['obj'] = $etablissement;
			}

			if(!isset($this->etlb[$typeEPLE][$nomEPLE]['classe'])) {
				$ClasseEleve = new Classe();
				$ClasseEleve->setLycee($eple);
				$ClasseEleve->setNom($classe);

				/** @var Lycee $lycee */
				$lycee = $this->etlb[$typeEPLE][$nomEPLE]["obj"];
				$lycee->addClass($ClasseEleve);

				$this->manager->persist($lycee);

				$this->etlb[$typeEPLE][$nomEPLE]["obj"] = $lycee;
			}

			//Le professeur n'a pas été enregistré en bdd
			if(!isset($this->etlb[$typeEPLE][$nomEPLE]['prof']['obj'])) {
				$nomProf = $page->getCell("G" . $index)->getFormattedValue();
				$prenomProf = $page->getCell('I' . $index)->getFormattedValue();
				$passProf = $page->getCell("H" . $index)->getFormattedValue();

				$prof = new User();
				$prof->setNom($nomProf);
				$prof->setPrenom($prenomProf);
				$prof->setPassword($passProf);
				$prof->setRoles(["ROLE_PROF"]);

				$this->manager->persist($prof);

				$this->etlb[$typeEPLE][$nomEPLE]['prof']['obj'] = $prof;
			}

			//Paramétrage de l'utilisateur
			$user->setPrenom($prenom);
			$user->setNom(implode(' ', $nom));
			$user->setPassword($pass);
			$user->setClasse($this->etlb[$typeEPLE][$nomEPLE]['classe']['obj']);
			$user->setUuid(Uuid::v4());

			$this->manager->persist($user);
			$this->manager->flush();

			$pBar->advance();
			$index++;
		}

		$this->manager->flush();
	}

	/**
	 * @param Participation[] $participations
	 * @param EntityManagerInterface $manager
	 * @return array
	 */
	public function calculateMeanResponseTime(array $participations, EntityManagerInterface $manager): array {
		$tmpArr = [];

		//Pour chaque utilisateurs dans $arrayList, on recherche tous les enregistrements, puis on calcule le temps de réponse moyen
		foreach ($participations as $p) {
			/** @var Record[] $records */
			$records = $manager->getRepository(Record::class)->findByParticipationId($p->getId());

			$responseTime = 0;
			$c = count($records);

			if($c == 0) {
				continue;
			}

			//Pour chaque participation
			foreach ($records as $enregistrement) {
				$responseTime += $enregistrement->getDuration();
			}

			$tmpArr[] = [
				"user" => $p->getUtilisateur()->getFancyName(),
				"time" => ($responseTime / $c),
				"score" => $p->getPoints()
			];
		}

		return $tmpArr;
	}
}
