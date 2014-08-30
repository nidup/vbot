<?php

namespace VBot\Bot\Engine;

use VBot\Game\Game;

/**
 * Engine interface, aims to update the hero depending on dedicated strategy
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface EngineInterface
{
    /**
     * @param Game $game
     */
    public function process(Game $game);
}
