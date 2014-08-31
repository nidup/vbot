<?php

namespace VBot\AStar;

use VBot\Game\Game;

/**
 * Path finder, forked from git@github.com:jmgq/php-a-star.git
 *
 * We don't use this library due to performance issues on big graphes, mainly due to
 * number of calls and use of objects and methods, we simplied it and, unfortunately,
 * make it less readable
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PathFinder
{
    /** @var boolean */
    const DEBUG = true;

    /** @var boolean */
    const USE_CPP_IMPL = true;

    /** @var Node[] */
    protected $openList;

    /** @var Node[] */
    protected $closedList;

    /** @var integer[] */
    protected $boardCosts;

    /**
     * @param integer[] $boardCosts
     */
    public function __construct(array $boardCosts)
    {
        $this->openList = [];
        $this->closedList = [];
        $this->boardCosts = $boardCosts;
    }

    /**
     * @param Node $start
     * @param Node $goal
     *
     * @return Node[]
     */
    public function find(Node $start, Node $goal)
    {
        if (self::USE_CPP_IMPL) {
            $startY = $start->getRow();
            $startX = $start->getColumn();
            $endY = $goal->getRow();
            $endX = $goal->getColumn();

            $width = count($this->boardCosts[0]);
            $height = count($this->boardCosts);
            $costs = [];
            foreach ($this->boardCosts as $rowCost) {
                $costs[]= implode(',', $rowCost);
            }
            $costs = implode(',', $costs);

            $cmd = sprintf(
                'bin/findpath %d %d %s %d %d %d %d',
                $width,
                $height,
                $costs,
                $startX,
                $startY,
                $endX,
                $endY
            );
            exec($cmd, $output, $return);

            if ($return !== 0) {
                throw new \Exception(sprintf('Path from %d:%d to %d:%d cannot be found with command %s', $startX, $startY, $endX, $endY, $cmd));
            }

            $path = [];
            $debug = '';
            foreach ($output as $token) {
                $coords = json_decode($token, true);
                $node = new Node($coords['y'], $coords['x']);
                // TODO : for now inject in the node object, we need to refactor the path finding
                $node->gScore = $coords['cost'];
                $path[]= $node;
                $debug[]= $coords['y'].':'.$coords['x'];
            }
            if (self::DEBUG) {
                echo implode(',', $debug).PHP_EOL;
            }

            return $path;

        } else {

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
    }

    /**
     * Replaces original demo implementation to avoid to deal with diagonals
     *
     * @param Node $node
     *
     * @return Node[]
     */
    protected function generateAdjacentNodes(Node $node)
    {
        $adjacentNodes = array();

        // top
        if ($node->row > 0) {
            $adjacentNodes[]= new Node($node->row - 1, $node->column);
        }
        // bottom
        if ($node->row < count($this->boardCosts) - 1) {
            $adjacentNodes[]= new Node($node->row + 1, $node->column);
        }
        // left
        if ($node->column > 0) {
            $adjacentNodes[]= new Node($node->row, $node->column - 1);
        }
        // right
        if ($node->column < count($this->boardCosts[0]) - 1) {
            $adjacentNodes[]= new Node($node->row, $node->column + 1);
        }

        return $adjacentNodes;
    }

    /**
     * @param Node $node
     * @param Node $adjacent
     *
     * @return integer
     */
    protected function calculateRealCost(Node $node, Node $adjacent)
    {
        $areAdjacent = abs($node->row - $adjacent->row) <= 1
            && abs($node->column - $adjacent->column) <= 1;

        if ($areAdjacent) {
            return $this->boardCosts[$adjacent->row][$adjacent->column];
        }

        return Game::MAX_COST;
    }

    /**
     * @param Node $start
     * @param Node $end
     *
     * @return integer
     */
    protected function calculateEstimatedCost(Node $start, Node $end)
    {
        $rowFactor = pow($start->row - $end->row, 2);
        $columnFactor = pow($start->column - $end->column, 2);

        $euclideanDistance = sqrt($rowFactor + $columnFactor);

        return $euclideanDistance;
    }

    /**
     * Sets the algorithm to its initial state
     */
    protected function clear()
    {
        $this->openList = [];
        $this->closedList = [];
    }

    /**
     * @param Node $node
     *
     * @return Node[]
     */
    protected function generatePathFromStartNodeTo(Node $node)
    {
        $path = array();

        $currentNode = $node;

        while ($currentNode !== null) {
            array_unshift($path, $currentNode);

            $currentNode = $currentNode->parent;
        }

        return $path;
    }

    /**
     * @param Node $node
     * @param Node $goal
     *
     * @return Node[]
     */
    protected function computeAdjacentNodes(Node $node, Node $goal)
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
