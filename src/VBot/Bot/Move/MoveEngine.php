<?php

namespace VBot\Bot\Move;

use VBot\Game\Hero;
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
    public function process(Board $board, Hero $hero)
    {
        if ($hero->getTarget() === null) {
            return 'Stay';
        }

        $path = $board->getShortestPath($hero, $hero->getTarget());
        if (!isset($path[1])) {
            return 'Stay';
        }

        $myPosX = $hero->getPosX();
        $myPosY = $hero->getPosY();

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
