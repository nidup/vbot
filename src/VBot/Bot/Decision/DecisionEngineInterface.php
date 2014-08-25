<?php

namespace VBot\Bot\Decision;

use VBot\Game\Game;
use VBot\Game\DestinationInterface;

/**
 * Decision engine interface, aims to choose the next target
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface DecisionEngineInterface
{
    /**
     * @param Game $game
     *
     * @return DestinationInterface|null
     */
    public function decide(Game $game);
}
