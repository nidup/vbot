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
        $target = $this->decisionEngine->decide($game);
        if ($target === null) {
            $destination = 'Stay';
        } else {
            $destination = $this->moveEngine->move($game->getBoard(), $game->getHero(), $target);
        }

        return $destination;
    }
}
