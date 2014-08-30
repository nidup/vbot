<?php

namespace VBot\AStar;

/**
 * Path printer, forked from git@github.com:jmgq/php-a-star.git
 *
 * We don't use this library due to performance issues on big graphes, mainly due to
 * number of calls and use of objects and methods, we simplied it and, unfortunately,
 * make it less readable
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class PathPrinter
{
    private $terrainCost;
    private $path;
    private $emptyTileToken = '-';
    private $tileSize = 3;
    private $padToken = ' ';

    /**
     * @param integer[] $terrainCost
     * @param Node[]    $path
     */
    public function __construct($terrainCost, array $path)
    {
        $this->terrainCost = $terrainCost;
        $this->path = $path;
    }

    public function printPath()
    {
        $board = $this->generateEmptyBoard();

        $step = 1;
        foreach ($this->path as $node) {
            $board[$node->getRow()][$node->getColumn()] = $this->getTile($step);

            $step++;
        }

        $stringBoard = array();

        foreach ($board as $row) {
            $stringBoard[] = implode('', $row);
        }

        echo implode("\n", $stringBoard);
    }

    /**
     * @return string
     */
    public function getEmptyTileToken()
    {
        return $this->emptyTileToken;
    }

    /**
     * @param string $emptyTileToken
     */
    public function setEmptyTileToken($emptyTileToken)
    {
        if (!is_string($emptyTileToken)) {
            throw new \InvalidArgumentException('Invalid empty tile token: ' . print_r($emptyTileToken, true));
        }

        $this->emptyTileToken = $emptyTileToken;
    }

    /**
     * @return int
     */
    public function getTileSize()
    {
        return $this->tileSize;
    }

    /**
     * @param int $tileSize
     */
    public function setTileSize($tileSize)
    {
        $naturalNumber = filter_var($tileSize, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1)));

        if ($naturalNumber === false) {
            throw new \InvalidArgumentException('Invalid tile size: ' . print_r($tileSize, true));
        }

        $this->tileSize = $naturalNumber;
    }

    /**
     * @return string
     */
    public function getPadToken()
    {
        return $this->padToken;
    }

    /**
     * @param string $padToken
     */
    public function setPadToken($padToken)
    {
        if (!is_string($padToken)) {
            throw new \InvalidArgumentException('Invalid pad token: ' . print_r($padToken, true));
        }

        $this->padToken = $padToken;
    }

    private function generateEmptyBoard()
    {
        $emptyTile = $this->getTile($this->getEmptyTileToken());

        $emptyRow = array_fill(0, count($this->terrainCost[0]), $emptyTile);

        $board = array_fill(0, count($this->terrainCost), $emptyRow);

        return $board;
    }

    private function getTile($value)
    {
        return str_pad($value, $this->getTileSize(), $this->getPadToken(), STR_PAD_LEFT);
    }
}
