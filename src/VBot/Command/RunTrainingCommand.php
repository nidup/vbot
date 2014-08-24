<?php

namespace VBot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use VBot\Starter\Client;

/**
 * Run the game
 *
 * @author  Nicolas Dupont <nicolas@akeneo.com>
 * @licence MIT
 */
class RunTrainingCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('run:training')
            ->setDescription('Run a Vindinium training')
            ->addArgument('key', InputArgument::REQUIRED, 'Secret user key')
            ->addArgument('turns', InputArgument::REQUIRED, 'Number of turns');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Run a Vindinium training');
        $output->writeln('');

        $client = new Client(
            $input->getArgument('key'),
            'training',
            $input->getArgument('turns')
        );
        $client->load();
    }
}
