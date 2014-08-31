<?php

namespace VBot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use VBot\Client\Client;
use VBot\Bot\BotFactory;

/**
 * Run an arena game
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RunArenaCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('run:arena')
            ->setDescription('Run a Vindinium arena')
            ->addArgument('key', InputArgument::REQUIRED, 'Secret user key')
            ->addOption('xhprof', null, InputOption::VALUE_NONE, 'Enable profiling with xhprof');
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('xhprof')) {
            xhprof_enable();
        }

        $output->writeln('Run a Vindinium arena');
        $output->writeln('');

        $botFactory = new BotFactory();
        $options = [
            'decision' => ['fsm_path' => realpath('./app/config/FSM/default.yml')]
        ];
        $bot = $botFactory->createBot($options);

        $client = new Client(
            $bot,
            $input->getArgument('key'),
            'arena',
            null,
            1,
            null
        );
        $client->load();

        if ($input->getOption('xhprof')) {
            $xhprofData = xhprof_disable();
            $XHPROF_ROOT = realpath('/usr/local/xhprof-0.9.4');
            include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
            include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
            $xhprofRuns = new \XHProfRuns_Default();
            $runId = $xhprofRuns->save_run($xhprofData, "xhprof_vbot");
            echo PHP_EOL.'xhprof run '.$runId.PHP_EOL;
        }
    }
}
