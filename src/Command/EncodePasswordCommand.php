<?php
namespace App\Command;

use App\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EncodePasswordCommand extends Command {

	protected static $defaultName = 'app:encode-pass';

	private UserPasswordEncoderInterface $encoder;

	public function __construct(UserPasswordEncoderInterface $encoder, string $name = null) {
		$this->encoder = $encoder;

		parent::__construct($name);
	}

	public function configure() {
		$this
			->setDescription('Return encoded password password')
			->addArgument('pass', InputArgument::REQUIRED);
	}

	public function execute(InputInterface $input, OutputInterface $output): int {
		$pass = $input->getArgument('pass');
		$user = new User();

		$output->writeln('Mot de passe : ' . $pass . " => " . $this->encoder->encodePassword($user, $pass));

		return Command::SUCCESS;
	}

}
