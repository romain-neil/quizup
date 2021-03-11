<?php
namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Choice;
use App\Entity\Participation;
use App\Entity\Question;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController {

	/**
	 * @Route("/", name="index")
	 * @return Response
	 */
	public function index(): Response {
		$user = $this->getUser();

		if (!$user) {
			return $this->redirectToRoute("app_login");
		}

		if ($this->isGranted('ROLE_PROF')) {
			//On redirige vers l'admin
			return $this->redirectToRoute('admin_show_questions');
		}

		return $this->render("index.html.twig", [
			"user" => $this->getUser()
		]);
	}

	/**
	 * Page de réponse d'une question,
	 * @Route("/repondre", name="repondre")
	 * @IsGranted("ROLE_USER")
	 * @param EntityManagerInterface $em
	 * @return Response
	 */
	public function repondre_question(EntityManagerInterface $em): Response {
		/** @var User $user */
		$user = $this->getUser();

		//On récupère la participation de l'utilisateur
		$participation = $user->getParticipation();

		/** @var Question[] $allQuestions */
		$allQuestions = (array) $em->getRepository(Question::class)->findAll();

		/** @var Choice[] $userChoices */
		$userChoices = $participation->getChoices(); //Récupération de toute les questions répondues

		/** @var Question $questionToShow */
		$questionToShow = null;

		if($participation != null) { //Si l'utilisateur a déja une participation enregistrée
			$questionsRepondues = [];

			if(count($userChoices) == count($allQuestions)) {
				//On a autant répondu à autant de questions que il y a de qestions

				$this->addFlash("success", "Vous avez répondu à toute les questions");

				return $this->redirectToRoute("index");
			}

			//On liste toutes les questions répondues par l'utilisateur
			foreach ($userChoices as $choice) {
				if(in_array($choice->getQuestion(), $allQuestions)) { //On a répondu a cette question
					$questionsRepondues[] = $choice->getQuestion()->getId();
				}
			}

			//Génération du nombre aléatoire
			$c = count($allQuestions);
			$c--;
			$id = rand(0, $c);

			//Si on a déja répondu à la question
			//On itère jusqu'à ce que l'on finisse par tomber sur une question non répondue
			if(in_array($id, $questionsRepondues)) {
				for($i = 0; $i < $c; $i++) {
					$id = rand($i, $c);
				}
			}

			$questionToShow = $allQuestions[$id];
		} else {
			//Première question à répondre

			/** @var Question $choice */
			$questionToShow = $allQuestions[rand(0, count($allQuestions))]; //Question avec id entre 0 et taille du tableau
		}

		//On mélange les réponses
		$answsers = (array) $em->getRepository(Answer::class)->findBy(["question" => $questionToShow]);
		shuffle($answsers);

		return $this->render('quiz/show_question.html.twig', [
			"question" => $questionToShow,
			"answers" => $answsers
		]);
	}

	/**
	 * Sauvegarde du choix de l'utilisateur
	 * @Route("/save", name="save_user_choice")
	 * @IsGranted("ROLE_USER")
	 * @param Request $request
	 * @param EntityManagerInterface $manager
	 * @return Response
	 */
	public function save_user_choice(Request $request, EntityManagerInterface $manager): Response {
		$choice = new Choice();

		/** @var Answer $answer */
		$answer = $manager->getRepository(Answer::class)->findOneBy(["id" => $request->query->get('reponse_id')]);

		$choice->addAnswer($answer);

		$manager->persist($choice);
		$manager->flush();

		/** @var Participation $participation */
		$participation = $this->getUser()->getParticipation();
		$choice->setParticipation($participation);

		//Si la réponse est correcte
		if($answer->getIsCorrect()) {
			$userPoints = $participation->getPoints();
			$userPoints++;

			$participation->setPoints($userPoints);
		}

		$manager->persist($choice);
		$manager->persist($participation);
		$manager->flush();

		//On redirige l'utilisateur vers la prochaine question
		return $this->redirectToRoute("repondre");
	}

}
