<?php

namespace VBot\AStar;

/**
 * Path finder, forked from git@github.com:jmgq/php-a-star.git
 * We don't use this library due to performance issues on big graphes
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PathFinder
{
    /** @var Node[] */
    protected $openList;

    /** @var Node[] */
    protected $closedList;

    /** @var TerrainCost */
    protected $terrainCost;

    /**
     * @param TerrainCost $terrainCost
     */
    public function __construct(TerrainCost $terrainCost)
    {
        $this->openList = [];
        $this->closedList = [];
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
            $adjacentNodes[]= new Node($node->getRow() - 1, $node->getColumn());
        }
        // bottom
        if ($node->getRow() < $this->terrainCost->getTotalRows() - 1) {
            $adjacentNodes[]= new Node($node->getRow() + 1, $node->getColumn());
        }
        // left
        if ($node->getColumn() > 0) {
            $adjacentNodes[]= new Node($node->getRow(), $node->getColumn() - 1);
        }
        // right
        if ($node->getColumn() < $this->terrainCost->getTotalColumns() - 1) {
            $adjacentNodes[]= new Node($node->getRow(), $node->getColumn() + 1);
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
     * Sets the algorithm to its initial state
     */
    public function clear()
    {
        $this->openList = [];
        $this->closedList = [];
    }

    /**
     * @param  Node   $start
     * @param  Node   $goal
     * @return Node[]
     */
    public function run(Node $start, Node $goal)
    {
        $path = array();

        $this->clear();

        $start->setG(0);
        $start->setH($this->calculateEstimatedCost($start, $goal));

        $this->openList[$start->getID()] = $start;

        while (!empty($this->openList)) {

            $currentNode = null;
            foreach ($this->openList as $node) {
                if ($currentNode === null || $node->getF() < $currentNode->getF()) {
                    $currentNode = $node;
                }
            }
            if ($currentNode !== null) {
                unset($this->openList[$currentNode->getID()]);
            }

            $this->closedList[$currentNode->getID()] = $currentNode;

            if ($currentNode->getID() === $goal->getID()) {
                $path = $this->generatePathFromStartNodeTo($currentNode);
                break;
            }

            $successors = $this->computeAdjacentNodes($currentNode, $goal);

            foreach ($successors as $successor) {
                if (isset($this->openList[$successor->getID()])) {
                    $successorInOpenList = $this->openList[$successor->getID()];

                    if ($successor->getG() >= $successorInOpenList->getG()) {
                        continue;
                    }
                }

                if (isset($this->closedList[$successor->getID()])) {
                    $successorInClosedList = $this->closedList[$successor->getID()];

                    if ($successor->getG() >= $successorInClosedList->getG()) {
                        continue;
                    }
                }

                unset($this->closedList[$successor->getID()]);

                $this->openList[$successor->getID()]= $successor;
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
