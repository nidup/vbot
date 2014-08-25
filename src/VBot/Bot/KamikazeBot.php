<?php

namespace VBot\Bot;

use VBot\AStar;

/**
 * Kamikaze Bot
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class KamikazeBot implements BotInterface
{
    public function move($state)
    {
        // my basic data
        $myPosX = (int) $state['hero']['pos']['x'];
        $myPosY = (int) $state['hero']['pos']['y'];
        $myId = $state['hero']['id'];

        // detect first enemy
        $enemyId = null;
        foreach ($state['game']['heroes'] as $heroData) {
            if ($heroData['id'] != $myId) {
                $enemyId = $heroData['id'];
                $enemyPosX = (int) $heroData['pos']['x'];
                $enemyPosY = (int) $heroData['pos']['y'];
                break;
            }
        }
       
        // find path to join the enemy
        $boardTiles = $state['game']['board']['tiles'];
        $boardSize = (int) $state['game']['board']['size'];
        $terrainCostFactory = new AStar\TerrainCostFactory();
        $terrainCost = $terrainCostFactory->create($boardTiles, $boardSize);
        $start = new AStar\MyNode($myPosY, $myPosX);
        $goal = new AStar\MyNode($enemyPosY, $enemyPosX);
        $aStar = new AStar\MyAStar($terrainCost);
        $solution = $aStar->run($start, $goal);

        $printer = new AStar\SequencePrinter($terrainCost, $solution);
        //$printer->printSequence();

        if (!isset($solution[1])) {
            return 'Stay';
        }

        $firstNode = $solution[1];
        $destX = (int) $firstNode->getColumn();
        $destY = (int) $firstNode->getRow();

        //echo PHP_EOL.'X dest > pos : '.$destX.' > '.$myPosX.PHP_EOL;
        //echo 'Y dest > pos : '.$destY.' > '.$myPosY.PHP_EOL;

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

        //var_dump($destination);

        return $destination;
    }
}
