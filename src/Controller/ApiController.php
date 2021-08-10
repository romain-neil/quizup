<?php
namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Classe;
use App\Entity\Lycee;
use App\Entity\Participation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Uid\Uuid;

/**
 * Class ApiController
 * @package App\Controller
 * @Route("/api", name="api_")
 */
class ApiController extends AbstractController {

	/**
	 * @Route("/parts")
	 * @param EntityManagerInterface $manager
	 * @return JsonResponse
	 */
	public function createAllParticipations(EntityManagerInterface $manager): JsonResponse {
		/** @var User[] $users */
		$users = $manager->getRepository(User::class)->findAll();

		foreach ($users as $user) {
			if($user->getParticipation() == null) {
				$part = new Participation();
				$part->setUtilisateur($user);

				$manager->persist($part);
			}
		}

		$manager->flush();

		return $this->json(["status" => "ok"]);
	}

	/**
	 * @Route("/create_user")
	 * @param UserPasswordEncoderInterface $encoder
	 * @return Response
	 */
	public function createUser(UserPasswordEncoderInterface $encoder): Response {
		$em = $this->getDoctrine()->getManager();

		/** @var Classe $SNIR2 */
		$SNIR2 = $em->getRepository(Classe::class)->findOneBy(["id" => 1]);

		$user = new User();

		$user->setPrenom("Bastien");
		$user->setNom("Biger");
		$user->setClasse($SNIR2);
		$user->setPassword($encoder->encodePassword($user, "pass"));
		$user->setUuid(Uuid::v4());

		$user->setRoles(["ROLE_ADMIN"]);

		$em->persist($user);
		$em->flush();

		$participation = new Participation();
		$participation->setUtilisateur($user);

		$em->persist($participation);
		$em->flush();

		return $this->json(["status" => "ok"]);
	}

	/**
	 * @Route("/class", name="get_class_list")
	 * @param Request $request
	 * @param EntityManagerInterface $manager
	 * @return JsonResponse
	 */
	public function getClassList(Request $request, EntityManagerInterface $manager): JsonResponse {
		$lyc_id = $request->query->get('lyc');
		$eple = $manager->getRepository(Lycee::class)->findBy(["id" => $lyc_id]);

		$allClass = $manager->getRepository(Classe::class)->findBy(["lycee" => $eple]);

		$tabClasses = [
			"status" => "success",
			"response" => []
		];

		foreach ($allClass as $classe) {
			$tabClasses["response"][$classe->getId()] = $classe->getNom();
		}

		if(sizeof($allClass) == 0) {
			$tabClasses['status'] = 'error';
			$tabClasses['message'] = "There is no class for the selected input";
		}

		return $this->json($tabClasses);
	}

	/**
	 * @Route("/answers", name="get_answers_list")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 * @param \Doctrine\ORM\EntityManagerInterface $manager
	 * @return \Symfony\Component\HttpFoundation\JsonResponse
	 */
	public function getAnswersList(Request $request, EntityManagerInterface $manager): JsonResponse {
		$questionId = $request->query->get('id');

		/** @var Answer[] $answers */
		$answers = $manager->getRepository(Answer::class)->findBy(["id" => $questionId]);

		return $this->json($answers);
	}

}
