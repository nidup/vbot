<?php

namespace VBot\Bot\Move;

use VBot\Game\DestinationInterface;
use VBot\Game\Board;

/**
 * Move engine interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class MoveEngine implements MoveEngineInterface
{
    /**
     * {@inheritDoc}
     */
    public function move(Board $board, DestinationInterface $start, DestinationInterface $target)
    {
        $path = $board->getShortestPath($start, $target);
        if (!isset($path[1])) {
            return 'Stay';
        }

        $myPosX = $start->getPosX();
        $myPosY = $start->getPosY();

        $firstNode = $path[1];
        $destX = (int) $firstNode->getRow();
        $destY = (int) $firstNode->getColumn();

        $direction = 'Stay';
        if ($destX > $myPosX) {
            $direction = 'South';

        } elseif ($destX < $myPosX) {
            $direction = 'North';

        } elseif ($destY > $myPosY) {
            $direction = 'East';

        } elseif ($destY < $myPosY) {
            $direction = 'West';
        }

        return $direction;
    }
}
