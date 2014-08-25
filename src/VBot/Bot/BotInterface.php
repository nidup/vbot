<?php

namespace VBot\Bot;

use VBot\Game\Game;

/**
 * Bot interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface BotInterface
{
    /**
     * @param Game $game
     *
     * @return string the direction
     */
    public function move(Game $game);
}
