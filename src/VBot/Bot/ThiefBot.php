<?php

namespace VBot\Bot;

use VBot\AStar;
use VBot\Game\Game;

/**
 * Thief Bot, follows a basic strategy, always attack the richest mine owner
 *
 * TODO : could be enhanced by going to the tavern
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ThiefBot implements BotInterface
{
    /**
     * {@inheritDoc}
     */
    public function move(Game $game)
    {
        // wait
        if ($game->getTurn() < 20) {
            return 'Stay';
        }

        // detect biggest mine owner
        $enemies = $game->getEnemies();
        if (empty($enemies)) {
            return 'Stay';
        }

        $target = null;
        foreach ($enemies as $enemy) {
            if ($target === null) {
                $target = $enemy;
            } elseif ($target->getMineCount() < $enemy->getMineCount()) {
                $target = $enemy;
            }
        }

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
