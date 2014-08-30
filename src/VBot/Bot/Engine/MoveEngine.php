<?php

namespace VBot\Bot\Engine;

use VBot\Game\Game;

/**
 * Find best path and inject the next direction to the hero
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class MoveEngine implements EngineInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(Game $game)
    {
        $hero = $game->getHero();
        if ($hero->getTarget() === null) {
            $hero->setDirection('Stay');

        } else {
            $board = $game->getBoard();
            $path = $board->getShortestPath($hero, $hero->getTarget());
            if (!isset($path[1])) {
                $hero->setDirection('Stay');

            } else {
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
                $hero->setDirection($direction);
            }
        }
    }
}
