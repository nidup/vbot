<?php

namespace VBot\Game;

use VBot\AStar\MyNode;
use VBot\AStar\MyAStar;
use VBot\AStar\TerrainCost;

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

    /** @var TerrainCost */
    protected $terrainCost = null;

    /** @varstatic string */
    const TAVERN = '[]';

    /** @varstatic string */
    const IMPASSABLE_WOOD = '##';

    /**
     * @param array $boardData
     */
    public function __construct(array $boardData)
    {
        $this->size = (int) $boardData['size'];
        $this->tiles = $boardData['tiles'];
        $this->parseTiles();
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
        $start = new MyNode($start->getPosX(), $start->getPosY());
        $destination = new MyNode($destination->getPosX(), $destination->getPosY());
        $aStar = new MyAStar($this->terrainCost);
        $path = $aStar->run($start, $destination);

        if (self::DEBUG) {
            $printer = new AStar\SequencePrinter($this->terrainCost, $path);
            $printer->printSequence();
        }

        return $path;
    }

    /**
     * Parse the tiles to detect mines and taverns
     *
     * TODO : different terrain costs as shortest, safest
     */
    protected function parseTiles()
    {
        $this->taverns = [];
        $this->mines = [];
        $tiles = str_split($this->tiles, 2);
        $indX = 0;
        $indY = 0;
        $cost = [];
        $rowCost = [];
        foreach ($tiles as $tile) {
            if ($tile === self::IMPASSABLE_WOOD) {
                $rowCost[]= PHP_INT_MAX;
            } elseif ($tile === self::TAVERN) {
                $rowCost[]= 6;
                $this->taverns[]= new Tavern($indX, $indY);
            } elseif (strpos($tile, '$') !== false) {
                $rowCost[]= 1;
                $this->mines[]= new Mine($indX, $indY, $tile[1]);
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
        $this->terrainCost = new TerrainCost($cost);
    }
}
