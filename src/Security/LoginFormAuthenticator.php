<?php
namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Guard\PasswordAuthenticatedInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator implements PasswordAuthenticatedInterface {

	use TargetPathTrait;

	public const LOGIN_ROUTE = 'app_login';

	private EntityManagerInterface $entityManager;
	private UrlGeneratorInterface $urlGenerator;
	private CsrfTokenManagerInterface $csrfTokenManager;
	private UserPasswordEncoderInterface $passwordEncoder;

	/**
	 * LoginFormAuthenticator constructor.
	 * @param EntityManagerInterface $entityManager
	 * @param UrlGeneratorInterface $urlGenerator
	 * @param CsrfTokenManagerInterface $csrfTokenManager
	 * @param UserPasswordEncoderInterface $passwordEncoder
	 */
	public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder) {
		$this->entityManager = $entityManager;
		$this->urlGenerator = $urlGenerator;
		$this->csrfTokenManager = $csrfTokenManager;
		$this->passwordEncoder = $passwordEncoder;
	}

	/**
	 * @param Request $request
	 * @return bool
	 */
	public function supports(Request $request): bool {
		return self::LOGIN_ROUTE === $request->attributes->get('_route')
			&& $request->isMethod('POST');
	}

	/**
	 * @param Request $request
	 * @return array
	 */
	public function getCredentials(Request $request): array {
		$credentials = [
			'prenom' => $request->request->get('prenom'),
			'nom' => $request->request->get('nom'),
			'password' => $request->request->get('password'),
			'classe' => $request->request->get('classe'),
			'csrf_token' => $request->request->get('_csrf_token'),
		];
		$request->getSession()->set(
			Security::LAST_USERNAME,
			$credentials['prenom']
		);

		return $credentials;
	}

	/**
	 * @param mixed $credentials
	 * @param UserProviderInterface $userProvider
	 * @return object|UserInterface|null
	 */
	public function getUser($credentials, UserProviderInterface $userProvider) {
		$token = new CsrfToken('authenticate', $credentials['csrf_token']);
		if (!$this->csrfTokenManager->isTokenValid($token)) {
			throw new InvalidCsrfTokenException();
		}

		$user = $this->entityManager->getRepository(User::class)->findOneBy([
			'prenom' => $credentials['prenom'],
			'nom' => $credentials['nom'],
			'classe' => $credentials['classe']
		]);

		if (!$user) {
			// fail authentication with a custom error
			throw new CustomUserMessageAuthenticationException('Username could not be found.');
		}

		return $user;
	}

	public function checkCredentials($credentials, UserInterface $user): bool {
		return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
	}

	/**
	 * Used to upgrade (rehash) the user's password automatically over time.
	 * @param $credentials
	 * @return string|null
	 */
	public function getPassword($credentials): ?string {
		return $credentials['password'];
	}

	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey): RedirectResponse {
		if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
			return new RedirectResponse($targetPath);
		}

		// For example : return new RedirectResponse($this->urlGenerator->generate('some_route'));
		return new RedirectResponse($this->urlGenerator->generate('index'));
	}

	protected function getLoginUrl(): string {
		return $this->urlGenerator->generate(self::LOGIN_ROUTE);
	}

}
