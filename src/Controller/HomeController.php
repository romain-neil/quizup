<?php
namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Choice;
use App\Entity\Participation;
use App\Entity\Question;
use App\Entity\Record;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
			return $this->redirectToRoute('admin_index');
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
		$allQuestions = (array) $em->getRepository(Question::class)->findAll();  //Liste de toutes les questions

		/** @var Choice[] $userChoices */
		$userChoices = $participation->getChoices(); //Récupération de toute les questions répondues

		/** @var Question $questionToShow */
		$questionToShow = null;

		if($participation != null) { //Si l'utilisateur a déja une participation enregistrée
			/** @var Question[] $questionsRepondues */
			$questionsRepondues = [];

			//Si on a répondu à autant de questions qu'il y en a
			if(count($userChoices) == count($allQuestions)) {
				//Alors on redirige l'utilisateur vers la page d'accueil
				$this->addFlash("success", "Vous avez répondu à toute les questions");

				return $this->redirectToRoute("index");
			}

			//On liste toutes les questions répondues par l'utilisateur
			foreach ($userChoices as $choice) {
				if(in_array($choice->getQuestion(), $allQuestions)) { //On a répondu a cette question
					$questionsRepondues[] = $choice->getQuestion();
				}
			}

			if(count($questionsRepondues) == 0) {
				//On a répondu à aucunne question
				$questionCount = count($allQuestions) - 1;

				$questionToShow = $allQuestions[rand(0, $questionCount)];
			} else {
				$potentialQuestions = [];

				//Tant que l'on a répondu à une question, on la retire des questions potentielles
				foreach ($allQuestions as $question) {
					if(!in_array($question, $questionsRepondues)) {
						//On ajoute question à la liste de question à poser
						array_push($potentialQuestions, $question);
					}
				}

				shuffle($potentialQuestions);

				$questionToShow = $potentialQuestions[0];
			}
		} else {
			//Première question à répondre

			/** @var Question $choice */
			$questionToShow = $allQuestions[rand(0, count($allQuestions))]; //Question avec id entre 0 et taille du tableau
		}

		//On mélange les réponses
		$answsers = (array) $em->getRepository(Answer::class)->findBy(["question" => $questionToShow]);
		shuffle($answsers);

		$this->get('session')->set('start', time());

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

		$start = (int)$this->get('session')->get('start');

		/** @var Answer $answer */
		$answer = $manager->getRepository(Answer::class)->findOneBy(["id" => $request->query->get('reponse_id')]);

		/** @var Question $question */
		$question = $manager->getRepository(Question::class)->findOneBy(['id' => $request->query->get('question_id')]);

		$choice->addAnswer($answer);
		$choice->setQuestion($question);

		$manager->persist($choice);
		$manager->flush();

		/** @var Participation $participation */
		$participation = $this->getUser()->getParticipation();
		$choice->setParticipation($participation);

		//Si la réponse est correcte
		if($answer->getIsCorrect() == true) {
			if($participation->getPoints() == null) {
				$participation->setPoints(0);
			}

			$userPoints = $participation->getPoints();
			$userPoints++;

			$participation->setPoints($userPoints);
		}

		$record = new Record();
		$record->setDuration((time() - $start));
		$record->setParticipationId($participation->getId());

		$manager->persist($record);
		$manager->persist($choice);
		$manager->persist($participation);
		$manager->flush();

		//On redirige l'utilisateur vers la prochaine question
		return $this->redirectToRoute("repondre");
	}

}
