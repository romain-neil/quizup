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

	public function __construct(EntityManagerInterface $manager) {
		$this->manager = $manager;
	}

	public function import(string $file, int $nb, ProgressBar $pBar) {
		$reader = new Xls();

		$spreedsheet = $reader->load($file);
		$spreedsheet->setActiveSheetIndex(0);

		for($i = 0; $i < $nb; $i++) { //Pour chaque ligne
			$page = $spreedsheet->getActiveSheet();

			$user = new User();

			$nomPrenom = $page->getCell("B" . $i)->getValue(); //DOE SPENCER John
			$pass = $page->getCell("C" . $i)->getCalculatedValue();
			$classe = $page->getCell("D" . $i)->getValue();

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
			$typeEPLE = $page->getCell("E" . $i)->getValue();
			$nomEPLE = $page->getCell("F" . $i)->getValue();

			//L'établissement n'a pas été enregistré en bdd
			if(!isset($etablissements[$typeEPLE], $etablissements[$typeEPLE][$nomEPLE]["obj"])) {
				$eple = new Lycee();

				$eple->setType($typeEPLE);
				$eple->setNom($nomEPLE);

				$this->manager->persist($eple);
				$this->manager->flush();

				$etablissements[$typeEPLE][$nomEPLE]["obj"] = $eple;
			}

			if(!isset($etablissements[$typeEPLE][$nomEPLE]["classe"])) {
				$ClasseEleve = new Classe();
				$ClasseEleve->setLycee($etablissements[$typeEPLE][$nomEPLE]);

				$etablissements[$typeEPLE][$nomEPLE]["classe"]["obj"] = $ClasseEleve;

				/** @var Lycee $lycee */
				$lycee = $etablissements[$typeEPLE][$nomEPLE]["obj"];
				$lycee->addClass($ClasseEleve);

				$this->manager->persist($lycee);

				$etablissements[$typeEPLE][$nomEPLE]["obj"] = $lycee;
			}

			if(!isset($etablissements[$typeEPLE][$nomEPLE]["classe"]["prof_obj"])) {
				$nomProf = $page->getCell("G" . $i); //TODO: fix me
				$passProf = $page->getCell("H" . $i);

				$prof = new User();
				$prof->setNom($nomProf);
				$prof->setPassword($passProf);
				$prof->setRoles(["ROLE_PROF"]);

				$this->manager->persist($prof);
			}

			//Le professeur n'a pas été enregistré en bdd

			//Récupération du professeur

			//Paramétrage de l'utilisateur
			$user->setPrenom($prenom);
			$user->setNom(implode(' ', $nom));
			$user->setPassword($pass);
			$user->setClasse($etablissements[$typeEPLE][$nomEPLE]["classe"]["obj"]);
			$user->setUuid(Uuid::v4());

			$this->manager->persist($user);

			$pBar->advance();
		}
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
