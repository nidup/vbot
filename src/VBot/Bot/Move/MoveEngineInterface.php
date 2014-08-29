<?php

namespace VBot\Bot\Move;

use VBot\Game\Hero;
use VBot\Game\Board;

/**
 * Move engine interface, aims to choose the best direction to reach the target
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface MoveEngineInterface
{
    /**
     * @param Board $board
     * @param Hero  $hero
     *
     * @return $direction
     */
    public function process(Board $board, Hero $hero);
}
