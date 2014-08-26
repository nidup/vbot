<?php

namespace VBot\AStar;

use JMGQ\AStar\AStar;
use JMGQ\AStar\Node;

// Quick attempt from git@github.com:jmgq/php-a-star.git
class MyAStar extends AStar
{
    private $terrainCost;

    public function __construct(TerrainCost $terrainCost)
    {
        $this->terrainCost = $terrainCost;
    }

    /**
     * Replaces original demo implementation to avoid to deal with diagonals
     * @inheritdoc
     */
    public function generateAdjacentNodes(Node $node)
    {
        $adjacentNodes = array();

        // top
        if ($node->getRow() > 0) {
            $adjacentNodes[]= new MyNode($node->getRow() - 1, $node->getColumn());
        }
        // bottom
        if ($node->getRow() < $this->terrainCost->getTotalRows() - 1) {
            $adjacentNodes[]= new MyNode($node->getRow() + 1, $node->getColumn());
        }
        // left
        if ($node->getColumn() > 0) {
            $adjacentNodes[]= new MyNode($node->getRow(), $node->getColumn() - 1);
        }
        // right
        if ($node->getColumn() < $this->terrainCost->getTotalColumns() - 1) {
            $adjacentNodes[]= new MyNode($node->getRow(), $node->getColumn() + 1);
        }

        return $adjacentNodes;
    }

    /**
     * @inheritdoc
     */
    public function calculateRealCost(Node $node, Node $adjacent)
    {
        $areAdjacent = abs($node->getRow() - $adjacent->getRow()) <= 1
            && abs($node->getColumn() - $adjacent->getColumn()) <= 1;

        if ($areAdjacent) {
            return $this->terrainCost->getCost($adjacent->getRow(), $adjacent->getColumn());
        }

        return TerrainCost::INFINITE;
    }

    /**
     * @inheritdoc
     */
    public function calculateEstimatedCost(Node $start, Node $end)
    {
        $rowFactor = pow($start->getRow() - $end->getRow(), 2);
        $columnFactor = pow($start->getColumn() - $end->getColumn(), 2);

        $euclideanDistance = sqrt($rowFactor + $columnFactor);

        return $euclideanDistance;
    }
}
