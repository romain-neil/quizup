<?php
namespace App\Command;

use App\Service\UserService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportUserCommand extends Command {

	protected static $defaultName = 'app:import-user';

	private UserService $userService;

	public function __construct(UserService $userService, string $name = null) {
		$this->userService = $userService;

		parent::__construct($name);
	}

	public function configure() {
		$this
			->setDescription('Importation d\'utilisateurs')
			->addArgument('file', InputArgument::REQUIRED);
	}

	public function execute(InputInterface $input, OutputInterface $output): int {
		$pBar = new ProgressBar($output);
		$pBar->setMaxSteps($input->getArgument('nb'));

		$this->userService->import(
			$input->getArgument('file'),
			$pBar
		);

		$pBar->finish();

		$output->writeln('<success>L\'import s\'est terminé sans problèmes</success>');

		return Command::SUCCESS;
	}

}
