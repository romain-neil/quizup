<?php
namespace App\Command;

use App\Service\QuestionService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportQuestionCommand extends Command {

	protected static $defaultName = 'app:import-question';

	/**
	 * @var QuestionService
	 */
	private QuestionService $service;

	public function __construct(QuestionService $service, string $name = null) {
		$this->service = $service;

		parent::__construct($name);
	}

	public function configure() {
		$this
			->setDescription('Importation de question')
			->addArgument('file', InputArgument::REQUIRED);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$progress = new ProgressBar($output);

		$this->service->inport(
			$input->getArgument('file'),
			$progress,
			$output
		);

		$progress->finish();

		return Command::SUCCESS;
	}

}
