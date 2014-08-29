<?php

namespace VBot\Bot\Decision;

use VBot\Game\Game;

/**
 * Decision engine interface, aims to choose the next target for the hero
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface DecisionEngineInterface
{
    /**
     * @param Game $game
     */
    public function process(Game $game);
}
