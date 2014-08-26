<?php

namespace VBot\AStar;

/**
 * Path finder, forked from git@github.com:jmgq/php-a-star.git
 *
 * We don't use this library due to performance issues on big graphes, mainly due to
 * number of calls and use of objects, we simplied it and, unfortunately, make it less readable
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
        if ($node->row > 0) {
            $adjacentNodes[]= new Node($node->row - 1, $node->column);
        }
        // bottom
        if ($node->row < $this->terrainCost->getTotalRows() - 1) {
            $adjacentNodes[]= new Node($node->row + 1, $node->column);
        }
        // left
        if ($node->column > 0) {
            $adjacentNodes[]= new Node($node->row, $node->column - 1);
        }
        // right
        if ($node->column < $this->terrainCost->getTotalColumns() - 1) {
            $adjacentNodes[]= new Node($node->row, $node->column + 1);
        }

        return $adjacentNodes;
    }

    /**
     * @inheritdoc
     */
    public function calculateRealCost(Node $node, Node $adjacent)
    {
        $areAdjacent = abs($node->row - $adjacent->row) <= 1
            && abs($node->column - $adjacent->column) <= 1;

        if ($areAdjacent) {
            return $this->terrainCost->getCost($adjacent->row, $adjacent->column);
        }

        return TerrainCost::INFINITE;
    }

    /**
     * @inheritdoc
     */
    public function calculateEstimatedCost(Node $start, Node $end)
    {
        $rowFactor = pow($start->row - $end->row, 2);
        $columnFactor = pow($start->column - $end->column, 2);

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

        $start->gScore = 0;
        $start->hScore = $this->calculateEstimatedCost($start, $goal);

        $this->openList[$start->id] = $start;

        while (!empty($this->openList)) {

            $currentNode = null;
            foreach ($this->openList as $node) {
                $nodeF = $node->gScore + $node->hScore;
                if ($currentNode === null || $nodeF < ($currentNode->gScore + $currentNode->hScore)) {
                    $currentNode = $node;
                }
            }
            if ($currentNode !== null) {
                unset($this->openList[$currentNode->id]);
            }

            $this->closedList[$currentNode->id] = $currentNode;

            if ($currentNode->id === $goal->id) {
                $path = $this->generatePathFromStartNodeTo($currentNode);
                break;
            }

            $successors = $this->computeAdjacentNodes($currentNode, $goal);

            foreach ($successors as $successor) {
                if (isset($this->openList[$successor->id])) {
                    $successorInOpenList = $this->openList[$successor->id];

                    if ($successor->gScore >= $successorInOpenList->gScore) {
                        continue;
                    }
                }

                if (isset($this->closedList[$successor->id])) {
                    $successorInClosedList = $this->closedList[$successor->id];

                    if ($successor->gScore >= $successorInClosedList->gScore) {
                        continue;
                    }
                }

                unset($this->closedList[$successor->id]);

                $this->openList[$successor->id]= $successor;
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

            $currentNode = $currentNode->parent;
        }

        return $path;
    }

    private function computeAdjacentNodes(Node $node, Node $goal)
    {
        $nodes = $this->generateAdjacentNodes($node);

        foreach ($nodes as $adjacentNode) {
            $adjacentNode->parent = $node;
            $adjacentNode->gScore = $node->gScore + $this->calculateRealCost($node, $adjacentNode);
            $adjacentNode->hScore = $this->calculateEstimatedCost($adjacentNode, $goal);
        }

        return $nodes;
    }
}
