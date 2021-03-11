<?php
namespace App\Controller;

use App\Entity\Lycee;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController {

	/**
	 * @Route("/login", name="app_login")
	 * @param AuthenticationUtils $authenticationUtils
	 * @param EntityManagerInterface $manager
	 * @return Response
	 */
	public function login(AuthenticationUtils $authenticationUtils, EntityManagerInterface $manager): Response {
		if ($this->getUser()) {
			return $this->redirectToRoute('index');
		}

		// get the login error if there is one
		$error = $authenticationUtils->getLastAuthenticationError();
		// last username entered by the user
		$lastMail = $authenticationUtils->getLastUsername();

		$listeEPLE = $manager->getRepository(Lycee::class)->findAll();

		return $this->render('security/login.html.twig', [
			'last_mail' => $lastMail,
			'error' => $error,
			'lycee' => $listeEPLE
		]);
	}

	/**
	 * @Route("/logout", name="app_logout")
	 */
	public function logout() {
		throw new LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
	}

}
