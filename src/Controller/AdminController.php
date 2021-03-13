<?php
namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Participation;
use App\Entity\Question;
use App\Entity\User;
use App\Form\QuestionType;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 * @Route("/admin", name="admin_")
 * @IsGranted("ROLE_PROF")
 * @package App\Controller
 */
class AdminController extends AbstractController {

	/**
	 * @Route("/show", name="show_questions")
	 * @return Response
	 */
	public function show_questions(): Response {
		$user = $this->getUser();

		$questions = $this->getDoctrine()->getRepository(Question::class)->findAll();

		return $this->render('admin/index.html.twig', ['user' => $user, "questions" => $questions]);
	}

	/**
	 * @Route("/add_question", name="ajout_question")
	 * @param Request $request
	 * @param FileUploader $fileUploader
	 * @param EntityManagerInterface $manager
	 * @return Response
	 */
	public function add_question(Request $request, FileUploader $fileUploader, EntityManagerInterface $manager): Response {
		$question = new Question();

		$form = $this->createForm(QuestionType::class, $question);
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid()) {
			if($form->has('image')) {
				/** @var UploadedFile $file */
				$file = $form->get('image')->getData();

				if($file) {
					$fileName = $fileUploader->upload($file);
					$question->setImage($fileName);
				}
			}

			$question = $this->setAnswers($question, $manager, $form);

			$manager->persist($question);
			$manager->flush();

			return $this->redirectToRoute("admin_show_questions");
		}

		return $this->render('admin/ajout_question.html.twig', ['form' => $form->createView()]);
	}

	/**
	 * WIP - Work In Progress
	 * @Route("/edit_question/{slug}", name="edit_question")
	 * @param Request $request
	 * @param string $slug
	 * @param EntityManagerInterface $manager
	 * @return Response
	 */
	public function edit_question(string $slug, Request $request, EntityManagerInterface $manager): Response {
		//On recherche la question

		/** @var Question $question */
		$question = $manager->getRepository(Question::class)->findOneBy(["id" => $slug]);

		if($request->getMethod() == Request::METHOD_POST) {
			//On sauvegarde avec les nouveaux paramètres
			$question->setLibele($request->request->get('libele'));

			//Loop through answers
			$question->flushAnswers();
			//$this->setAnswers($question, $request, $manager); //TODO: fix me

			$manager->persist($question);
			$manager->flush();

			//$this->getDoctrine()->getManager(Question::class)->persist($question);
			//$this->getDoctrine()->getManager(Question::class)->flush();

			return $this->redirectToRoute('admin_show_questions');
		} else {
			if($question != null) {
				//On affiche le formulaire d'édition
				return $this->render('', ['question' => $question]);
			}

			return $this->redirectToRoute("admin_show_questions");
		}
	}

	/**
	 * @Route("/delete/{id}", name="question_delete")
	 * @param int $id
	 * @param EntityManagerInterface $manager
	 * @return Response
	 */
	public function delete_question(int $id, EntityManagerInterface $manager): Response {
		$repo = $this->getDoctrine()->getRepository(Question::class);
		$question = $repo->find($id);

		$manager->remove($question);
		$manager->flush();

		return $this->redirectToRoute("admin_show_questions");
	}

	/**
	 * @Route("/best_scores", name="show_high_scores")
	 * @IsGranted("ROLE_PROF")
	 * @param Request $request
	 * @param EntityManagerInterface $manager
	 * @return Response
	 */
	public function show_high_scores(Request $request, EntityManagerInterface $manager): Response {
		$participations = $manager->getRepository(Participation::class)->findAll();

		/** @var User[] $allUsers */
		$allUsers = $manager->getRepository(User::class)->findAll();

		//Si on est enseignant, afficher seulement les participations de sa classe
		if($request->query->get('show') == "class") {
			//filter participation which are not null, sort scores, give them to the template
			$part = $this->filterParticipationComplete($participations, $allUsers);
		} else {
			$part = $this->filterParticipationComplete($participations, $allUsers, true);
		}

		rsort($part);

		return $this->render('admin/high_scores.html.twig', ['liste' => $part]);
	}

	/**
	 * Définie la réponse pour la question correspondante
	 * @param Question $question
	 * @param EntityManagerInterface $manager
	 * @param FormInterface $form
	 * @return Question
	 */
	private function setAnswers(Question $question, EntityManagerInterface $manager, FormInterface $form): Question {
		foreach (range('a', 'd') as $letter) {
			$ans = new Answer();
			$ans->setLibele($form->get('answer-lbl-' . $letter)->getData());

			if($form->has('answer-rd' . $letter)) {
				$ans->setIsCorrect(true);
			} else {
				$ans->setIsCorrect(false);
			}

			$manager->persist($ans);

			$question->addAnswer($ans);
		}

		return $question;
	}

	/**
	 * @param array $participation
	 * @param User[] $users
	 * @param bool $showAllClass
	 * @return array
	 */
	private function filterParticipationComplete(array $participation, array $users, bool $showAllClass = false): array {
		$tempArray = [];
		$c = count($participation);

		$currentUser = $this->getUser();

		for($i = 0; $i < $c; $i++) {
			$points = $participation[$i]->getPoints();
			if($points != null) {
				//Si la classe de l'utilisateur est la même que l'utilisateur de la boucle, ou que l'on souhaite afficher toutes les classes
				//Alors on récupère les paramètres de l'utilisateur sur lequel on boucle
				if($showAllClass || $currentUser->getClasse() == $users[$i]->getClasse()) {
					$tempArray[] = [
						"score" => $points,
						"fancy_name" => $users[$i]->getFancyName(),
						"user_class" => $users[$i]->getClasse()->getNom()
					];
				}
			}
		}

		return $tempArray;
	}

}