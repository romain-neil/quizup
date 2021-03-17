<?php
namespace App\Command;

use App\Entity\User;
use App\Service\UserService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EncodeAllPasswordInDBCommand extends Command {

	protected static $defaultName = 'app:encode-all-pass';

	private UserService $service;

	private UserPasswordEncoderInterface $encoder;
	private EntityManagerInterface $manager;

	public function __construct(UserService $service, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager, string $name = null) {
		$this->service = $service;
		$this->encoder = $encoder;
		$this->manager = $manager;

		parent::__construct($name);
	}

	public function configure() {
		$this
			->setDescription('Encode tout les mdp de la base de donnée');
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		/** @var User[] $users */
		$users = $this->manager->getRepository(User::class)->findAll();

		foreach ($users as $user) {
			$user->setPassword(
				$this->encoder->encodePassword($user, $user->getPassword())
			);

			$this->manager->persist($user);
		}

		$this->manager->flush();

		return Command::SUCCESS;
	}

}