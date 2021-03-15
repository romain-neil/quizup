<?php
namespace App\Controller;

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

		return $this->json([$tabClasses]);
	}

	/**
	 * @Route("/dump_form")
	 * @param Request $request
	 * @return Response
	 */
	public function show_req_params(Request $request): Response {
		return $this->render("debug.html.twig", ["file" => $request->request]);
	}

}
