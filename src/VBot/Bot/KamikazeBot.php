<?php

namespace VBot\Bot;

use VBot\AStar;
use VBot\Game\Game;

/**
 * Kamikaze Bot
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class KamikazeBot implements BotInterface
{
    /**
     * {@inheritDoc}
     */
    public function move(Game $game)
    {
        // detect first enemy
        $enemies = $game->getEnemies();
        if (empty($enemies)) {
            return 'Stay';
        }
        $target = current($enemies);

        // find path to join the enemy
        $myPosX = $game->getHero()->getPosX();
        $myPosY = $game->getHero()->getPosY();
        $terrainCostFactory = new AStar\TerrainCostFactory();
        $terrainCost = $terrainCostFactory->create($game->getBoard());
        $start = new AStar\MyNode($myPosX, $myPosY);
        $goal = new AStar\MyNode($target->getPosX(), $target->getPosY());
        $aStar = new AStar\MyAStar($terrainCost);
        $solution = $aStar->run($start, $goal);
        //$printer = new AStar\SequencePrinter($terrainCost, $solution);
        //$printer->printSequence();

        if (!isset($solution[1])) {
            return 'Stay';
        }

        $firstNode = $solution[1];
        $destX = (int) $firstNode->getRow();
        $destY = (int) $firstNode->getColumn();

        $destination = 'Stay';

        if ($destX > $myPosX) {
            $destination = 'South';

        } elseif ($destX < $myPosX) {
            $destination = 'North';

        } elseif ($destY > $myPosY) {
            $destination = 'East';

        } elseif ($destY < $myPosY) {
            $destination = 'West';
        }

        return $destination;
    }
}
