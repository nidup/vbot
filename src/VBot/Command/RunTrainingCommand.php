<?php

namespace VBot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use VBot\Client\Client;
use VBot\Bot\BotFactory;

/**
 * Run a training game
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
            ->addArgument('turns', InputArgument::REQUIRED, 'Number of turns')
            ->addArgument('map', InputArgument::REQUIRED, 'Map to use (m1, m2, m3, m4, m5, m6)');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Run a Vindinium training');
        $output->writeln('');

        $botFactory = new BotFactory();
        $options = [
            'decision' => ['fsm_path' => realpath('./app/config/FSM/default.yml')]
        ];
        $bot = $botFactory->createBot($options);

        $client = new Client(
            $bot,
            $input->getArgument('key'),
            'training',
            $input->getArgument('turns'),
            1,
            $input->getArgument('map')
        );
        $client->load();
    }
}
