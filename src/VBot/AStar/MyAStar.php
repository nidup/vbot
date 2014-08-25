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
        $myNode = MyNode::fromNode($node);

        // top
        if ($myNode->getRow() > 0) {
            $adjacentNodes[]= new MyNode($myNode->getRow() - 1, $myNode->getColumn());
        }
        // bottom
        if ($myNode->getRow() < $this->terrainCost->getTotalRows() - 1) {
            $adjacentNodes[]= new MyNode($myNode->getRow() + 1, $myNode->getColumn());
        }
        // left
        if ($myNode->getColumn() > 0) {
            $adjacentNodes[]= new MyNode($myNode->getRow(), $myNode->getColumn() - 1);
        }
        // right
        if ($myNode->getColumn() < $this->terrainCost->getTotalColumns() - 1) {
            $adjacentNodes[]= new MyNode($myNode->getRow(), $myNode->getColumn() + 1);
        }

        return $adjacentNodes;
    }

    /**
     * @inheritdoc
     */
    public function calculateRealCost(Node $node, Node $adjacent)
    {
        $myStartNode = MyNode::fromNode($node);
        $myEndNode = MyNode::fromNode($adjacent);

        if ($this->areAdjacent($myStartNode, $myEndNode)) {
            return $this->terrainCost->getCost($myEndNode->getRow(), $myEndNode->getColumn());
        }

        return TerrainCost::INFINITE;
    }

    /**
     * @inheritdoc
     */
    public function calculateEstimatedCost(Node $start, Node $end)
    {
        $myStartNode = MyNode::fromNode($start);
        $myEndNode = MyNode::fromNode($end);

        $rowFactor = pow($myStartNode->getRow() - $myEndNode->getRow(), 2);
        $columnFactor = pow($myStartNode->getColumn() - $myEndNode->getColumn(), 2);

        $euclideanDistance = sqrt($rowFactor + $columnFactor);

        return $euclideanDistance;
    }

    private function areAdjacent(MyNode $a, MyNode $b)
    {
        return abs($a->getRow() - $b->getRow()) <= 1 && abs($a->getColumn() - $b->getColumn()) <= 1;
    }
}
