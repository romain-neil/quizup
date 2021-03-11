<?php
require 'vendor/autoload.php';

use App\Entity\Classe;
use App\Entity\Lycee;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use Symfony\Component\Uid\Uuid;

/* Doctrine */
// Configuration par defaut Doctrine ORM avec avec Annotations
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/src"), $isDevMode);
// En yaml ou en  XML
//$config = Setup::createXMLMetadataConfiguration(array(__DIR__."/config/xml"), $isDevMode);
//$config = Setup::createYAMLMetadataConfiguration(array(__DIR__."/config/yaml"), $isDevMode);

// Parametres de la database
$conn = array(
	'driver' => 'pdo_mysql',
	'user' => 'root',
	'password' => '',
	'dbname' =>'testdoctrine',
);

// Obtenir l'Entity Manager
$entityManager = EntityManager::create($conn, $config);


$inputFile = 'nom_du_fichier.xls';
$max_students = 50;

$reader = new Xls();

$spreedsheet = $reader->load($inputFile);
$spreedsheet->setActiveSheetIndex(0);

$eleves = [];
$etablissements = [];

for($i = 0; $i < $max_students; $i++) {
	//Pour chaque ligne

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

		$entityManager->persist($eple);
		$entityManager->flush();

		$etablissements[$typeEPLE][$nomEPLE]["obj"] = $eple;
	}

	if(!isset($etablissements[$typeEPLE][$nomEPLE]["classe"])) {
		$ClasseEleve = new Classe();
		$ClasseEleve->setLycee($etablissements[$typeEPLE][$nomEPLE]);

		$etablissements[$typeEPLE][$nomEPLE]["classe"]["obj"] = $ClasseEleve;

		/** @var Lycee $lycee */
		$lycee = $etablissements[$typeEPLE][$nomEPLE]["obj"];
		$lycee->addClass($ClasseEleve);

		$entityManager->persist($lycee);

		$etablissements[$typeEPLE][$nomEPLE]["obj"] = $lycee;
	}

	if(!isset($etablissements[$typeEPLE][$nomEPLE]["classe"]["prof_obj"])) {
		$nomProf = $page->getCell("G" . $i); //TODO: fix me
		$passProf = $page->getCell("H" . $i);

		$prof = new User();
		$prof->setNom($nomProf);
		$prof->setPassword($passProf);
		$prof->setRoles(["ROLE_PROF"]);

		$entityManager->persist($prof);
	}

	//Le professeur n'a pas été enregistré en bdd

	//Récupération du professeur

	//Paramétrage de l'utilisateur
	$user->setPrenom($prenom);
	$user->setNom(implode(' ', $nom));
	$user->setPassword($pass);
	$user->setClasse($etablissements[$typeEPLE][$nomEPLE]["classe"]["obj"]);
	$user->setUuid(Uuid::v4());

	$entityManager->persist($user);
}

$entityManager->flush();
