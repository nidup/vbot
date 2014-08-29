<?php

namespace VBot\Bot;

use VBot\Game\Game;
use VBot\Bot\Move\MoveEngineInterface;
use VBot\Bot\Decision\DecisionEngineInterface;

/**
 * Finite State Machine Bot
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class FSMBot implements BotInterface
{
    /** @var boolean */
    const DEBUG = true;

    /** @var DecisionEngineInterface */
    protected $decisionEngine;

    /** @var MoveEngineInterface */
    protected $moveEngine;

    /**
     * @param DecisionEngineInterface $decisionEngine
     * @param MoveEngineInterface     $moveEngine
     */
    public function __construct(DecisionEngineInterface $decisionEngine, MoveEngineInterface $moveEngine)
    {
        $this->decisionEngine = $decisionEngine;
        $this->moveEngine = $moveEngine;
    }

    /**
     * {@inheritDoc}
     */
    public function move(Game $game)
    {
        if (self::DEBUG) {
            $timeStart = microtime(true);
            $hero = $game->getHero();
            echo sprintf(
                'Turn: %d/%d Life: %d Gold: %d Pos x:y : %d:%d Leader: %s Victory ratio: %d',
                $game->getTurn(),
                $game->getMaxTurns(),
                $hero->getLife(),
                $hero->getGold(),
                $hero->getPosX(),
                $hero->getPosY(),
                ($game->getRanking()->isLeader($hero)) ? 'Yes' : 'No',
                $game->getRanking()->getVictoryRatio($hero)
            ).PHP_EOL;
        }

        $this->decisionEngine->process($game);
        $destination = $this->moveEngine->process($game->getBoard(), $game->getHero());

        if (self::DEBUG) {
            $timeEnd = microtime(true);
            $time = $timeEnd - $timeStart;
            $memory = memory_get_peak_usage() / 1024 / 1024;
            // echo sprintf('Time: %s sec, Memory: %s M', number_format($time, 2), number_format($memory, 2)).PHP_EOL;
        }

        return $destination;
    }
}
