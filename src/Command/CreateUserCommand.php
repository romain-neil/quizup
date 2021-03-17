<?php
namespace App\Command;

use App\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserCommand extends Command {

	protected static $defaultName = 'app:create-user';

	private UserService $userService;

	public function __construct(UserService $service, string $name = null) {
		$this->userService = $service;

		parent::__construct($name);
	}

	public function configure() {
		$this
			->setDescription("Création d'utilisateur")
			->addArgument('nom', InputArgument::REQUIRED)
			->addArgument('prenom', InputArgument::REQUIRED)
			->addArgument('id-classe', InputArgument::REQUIRED, 'Identifiant de la classe de l\'utilisateur');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$output->writeln("<comment>Création de l'utilisateur ...</comment>");

		if(!$this->userService->createUser($input->getArgument('nom'), $input->getArgument('prenom'), (int)$input->getArgument('id-classe'))) {
			$output->writeln("<error>La classe avec cet id n'existe pas</error>");

			return Command::FAILURE;
		}


		$output->writeln("<info>Utilisateur créer avec succès</info>");

		return Command::SUCCESS;
	}

}
