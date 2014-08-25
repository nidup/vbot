<?php

namespace VBot\Bot\Move;

// TODO use position class ?
use VBot\Game\DestinationInterface;
use VBot\Game\Board;
use VBot\AStar;

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
        $myPosX = $start->getPosX();
        $myPosY = $start->getPosY();
        $terrainCostFactory = new AStar\TerrainCostFactory();
        $terrainCost = $terrainCostFactory->create($board);
        $start = new AStar\MyNode($myPosX, $myPosY);
        $goal = new AStar\MyNode($target->getPosX(), $target->getPosY());
        $aStar = new AStar\MyAStar($terrainCost);
        $solution = $aStar->run($start, $goal);
        // TODO debugger purpose
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
