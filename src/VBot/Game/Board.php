<?php

namespace VBot\Game;

use VBot\AStar\Node;
use VBot\AStar\PathFinder;

/**
 * Board model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Board
{
    /** @var boolean */
    const DEBUG = false;

    /** @var integer */
    protected $size;

    /** @var string */
    protected $tiles;

    /** @var Tavern[] */
    protected $taverns = null;

    /** @var Mine[] */
    protected $mines = null;

    /** @var integer[] */
    protected $terrainCost = null;

    /** @varstatic string */
    const TAVERN = '[]';

    /** @varstatic string */
    const IMPASSABLE_WOOD = '##';

    /**
     * Initialize the board state and setup all models
     *
     * @param array $boardData
     */
    public function __construct(array $boardData)
    {
        $this->size = (int) $boardData['size'];
        $this->tiles = $boardData['tiles'];
        $this->createTaverns();
        $this->createMines();
        $this->updateTerrainCost();
    }

    /**
     * Update the board state, update only what may change
     *
     * @param array $boardData
     */
    public function update($boardData)
    {
        $this->tiles = $boardData['tiles'];
        $this->updateMines();
        $this->updateTerrainCost();
    }

    /**
     * @return string
     */
    public function getTiles()
    {
        return $this->tiles;
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return Tavern[]
     */
    public function getTaverns()
    {
        return $this->taverns;
    }

    /**
     * @return Mine[]
     */
    public function getMines()
    {
        return $this->mines;
    }

    /**
     * Get closest destination
     *
     * @param DestinationInterface   $start
     * @param DestinationInterface[] $destinations
     *
     * @return DestinationInterface
     */
    public function getClosestDestination(DestinationInterface $start, array $destinations)
    {
        $pathLength = PHP_INT_MAX;
        $closest = null;
        foreach ($destinations as $destination) {
            $path = $this->getShortestPath($start, $destination);
            if (count($path) < $pathLength) {
                $pathLength = count($path);
                $closest = $destination;
            }
        }

        return $closest;
    }

    /**
     * Get shortest path
     *
     * @param DestinationInterface $start
     * @param DestinationInterface $destination
     *
     * @return Node[]
     */
    public function getShortestPath(DestinationInterface $start, DestinationInterface $destination)
    {
        $start = new Node($start->getPosX(), $start->getPosY());
        $destination = new Node($destination->getPosX(), $destination->getPosY());
        $aStar = new PathFinder($this->terrainCost);
        $path = $aStar->run($start, $destination);

        if (self::DEBUG) {
            $printer = new AStar\SequencePrinter($this->terrainCost, $path);
            $printer->printSequence();
        }

        return $path;
    }

    /**
     * Parse the tiles to create taverns
     */
    protected function createTaverns()
    {
        $this->taverns = [];
        $tiles = str_split($this->tiles, 2);
        $indX = 0;
        $indY = 0;
        foreach ($tiles as $tile) {
            if ($tile === self::TAVERN) {
                $this->taverns[]= new Tavern($indX, $indY);
            }
            if (++$indY % $this->size === 0) {
                $indX++;
                $indY = 0;
            }
        }
    }

    /**
     * Create mines
     */
    protected function createMines()
    {
        $this->mines = [];
        $tiles = str_split($this->tiles, 2);
        $indX = 0;
        $indY = 0;
        foreach ($tiles as $tile) {
            if (strpos($tile, '$') !== false) {
                $this->mines[]= new Mine($indX, $indY, $tile[1]);
            }
            if (++$indY % $this->size === 0) {
                $indX++;
                $indY = 0;
            }
        }
    }

    /**
     * Update mines
     */
    protected function updateMines()
    {
        $tiles = str_split($this->tiles, 2);
        $indMine = 0;
        foreach ($tiles as $tile) {
            if (strpos($tile, '$') !== false) {
                $mine = $this->mines[$indMine];
                $mine->update($tile[1]);
                $indMine++;
            }
        }
    }

    /**
     * Update terrain cost
     *
     * TODO : different terrain costs as shortest, safest
     * TODO : cost depends on map size and configuration
     */
    protected function updateTerrainCost()
    {
        $tiles = str_split($this->tiles, 2);
        $indX = 0;
        $indY = 0;
        $cost = [];
        $rowCost = [];
        foreach ($tiles as $tile) {
            if ($tile === self::IMPASSABLE_WOOD) {
                $rowCost[]= PHP_INT_MAX;
            } elseif ($tile === self::TAVERN) {
                $rowCost[]= 50;
            } elseif (strpos($tile, '$') !== false) {
                $rowCost[]= 50;
            } elseif (strpos($tile, '@') !== false) {
                $rowCost[]= 10;
            } else {
                $rowCost[]= 1;
            }
            if (++$indY % $this->size === 0) {
                $indX++;
                $indY = 0;
                $cost[]= $rowCost;
                $rowCost = [];
            }
        }
        $this->terrainCost = $cost;
    }
}
