<?php

namespace VBot\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use VBot\Bot\BotFactory;
use VBot\Game\Game;

/**
 * Run a debug command, aims to fix issue on performance
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RunDebugCommand extends Command
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('run:debug')
            ->setDescription('Run a debug command to fix perf issues')
            ->addArgument('json-state', InputArgument::REQUIRED, 'The game state in json')
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

        $output->writeln('Run a AStar path finding');
        $output->writeln('');

        $botFactory = new BotFactory();
        $options = [
            'decision' => ['fsm_path' => realpath('./app/config/FSM/default.yml')]
        ];
        $bot = $botFactory->createBot($options);

        $jsonState = $input->getArgument('json-state');
        $state = json_decode($jsonState, true);
        $game  = new Game($state);
        $mine = $game->getClosestNotOwnedMine($game->getHero());
        var_dump($mine);

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
