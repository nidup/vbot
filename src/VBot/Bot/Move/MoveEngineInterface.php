<?php

namespace VBot\Bot\Move;

use VBot\Game\DestinationInterface;
use VBot\Game\Board;

/**
 * Move engine interface, aims to choose the best direction to reach the target
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface MoveEngineInterface
{
    /**
     * @param Board                $board
     * @param DestinationInterface $start
     * @param DestinationInterface $target
     *
     * @return $direction
     */
    public function move(Board $board, DestinationInterface $start, DestinationInterface $target);
}
