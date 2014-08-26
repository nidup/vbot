<?php

namespace VBot\AStar;

use JMGQ\AStar\Node;
use JMGQ\AStar\NodeList;
use JMGQ\AStar\Algorithm;

// Quick attempt from git@github.com:jmgq/php-a-star.git
// Not use callback algorithm due to extensible use of call user func
class MyAlgorithm
{
    private $openList;
    private $closedList;
    private $terrainCost;

    public function __construct(TerrainCost $terrainCost)
    {
        $this->openList = new NodeList();
        $this->closedList = new NodeList();
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

    /**
     * @return NodeList
     */
    public function getOpenList()
    {
        return $this->openList;
    }

    /**
     * @return NodeList
     */
    public function getClosedList()
    {
        return $this->closedList;
    }

    /**
     * Sets the algorithm to its initial state
     */
    public function clear()
    {
        $this->getOpenList()->clear();
        $this->getClosedList()->clear();
    }

    /**
     * @param Node $start
     * @param Node $goal
     * @return Node[]
     */
    public function run(Node $start, Node $goal)
    {
        $path = array();

        $this->clear();

        $start->setG(0);
        $start->setH($this->calculateEstimatedCost($start, $goal));

        $this->getOpenList()->add($start);

        while (!$this->getOpenList()->isEmpty()) {
            $currentNode = $this->getOpenList()->extractBest();

            $this->getClosedList()->add($currentNode);

            if ($currentNode->getID() === $goal->getID()) {
                $path = $this->generatePathFromStartNodeTo($currentNode);
                break;
            }

            $successors = $this->computeAdjacentNodes($currentNode, $goal);

            foreach ($successors as $successor) {
                if ($this->getOpenList()->contains($successor)) {
                    $successorInOpenList = $this->getOpenList()->get($successor);

                    if ($successor->getG() >= $successorInOpenList->getG()) {
                        continue;
                    }
                }

                if ($this->getClosedList()->contains($successor)) {
                    $successorInClosedList = $this->getClosedList()->get($successor);

                    if ($successor->getG() >= $successorInClosedList->getG()) {
                        continue;
                    }
                }

                $this->getClosedList()->remove($successor);

                $this->getOpenList()->add($successor);
            }
        }

        return $path;
    }

    private function generatePathFromStartNodeTo(Node $node)
    {
        $path = array();

        $currentNode = $node;

        while ($currentNode !== null) {
            array_unshift($path, $currentNode);

            $currentNode = $currentNode->getParent();
        }

        return $path;
    }

    private function computeAdjacentNodes(Node $node, Node $goal)
    {
        $nodes = $this->generateAdjacentNodes($node);

        foreach ($nodes as $adjacentNode) {
            $adjacentNode->setParent($node);
            $adjacentNode->setG($node->getG() + $this->calculateRealCost($node, $adjacentNode));
            $adjacentNode->setH($this->calculateEstimatedCost($adjacentNode, $goal));
        }

        return $nodes;
    }
}
