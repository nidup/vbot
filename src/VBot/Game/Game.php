<?php

namespace VBot\Game;

use VBot\AStar\BoardCostsFactory;
use VBot\AStar\Node;
use VBot\AStar\PathFinder;
use VBot\AStar\PathPrinter;

/**
 * Game model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Game
{
    /** @var string */
    protected $id;

    /** @var integer */
    protected $turn;

    /** @var integer */
    protected $maxTurns;

    /** @var AbstractPlayer[] */
    protected $players;

    /** @var Enemy[] */
    protected $enemies;

    /** @var Hero */
    protected $hero;

    /** @var Tavern[] */
    protected $taverns = null;

    /** @var Mine[] */
    protected $mines = null;

    /** @var integer */
    protected $boardSize;

    /** @var string */
    protected $boardTiles;

    /** @var array */
    protected $boardCosts;

    /** @var boolean */
    protected $finished;

    /** @var Ranking */
    protected $ranking;

    /** @var string */
    protected $token;

    /** @var string */
    protected $viewUrl;

    /** @var string */
    protected $playUrl;

    /** @varstatic string */
    const TAVERN = '[]';

    /** @varstatic string */
    const IMPASSABLE_WOOD = '##';

    /** @var boolean */
    const DEBUG = false;

    /**
     * Initialize the game state and setup all models
     *
     * @param array $gameData
     */
    public function __construct(array $gameData)
    {
        $this->id = $gameData['game']['id'];
        $this->turn = $gameData['game']['turn'];
        $this->maxTurns = $gameData['game']['maxTurns'];
        $this->finished = $gameData['game']['finished'];
        $this->enemies = [];
        foreach ($gameData['game']['heroes'] as $playerData) {
            if ($playerData['id'] !== $gameData['hero']['id']) {
                $this->enemies[]= new Enemy($playerData);
            }
        }
        $this->boardTiles = $gameData['game']['board']['tiles'];
        $this->boardSize = (int) $gameData['game']['board']['size'];
        $this->createTaverns();
        $this->createMines();
        $this->hero = new Hero($gameData['hero']);
        $this->players = array_merge($this->enemies, [$this->hero]);
        $this->ranking = new Ranking($this);
        $this->token = $gameData['token'];
        $this->viewUrl = $gameData['viewUrl'];
        $this->playUrl = $gameData['playUrl'];
        $this->updateBoardCosts();
    }

    /**
     * Parse the tiles to create taverns
     */
    protected function createTaverns()
    {
        $this->taverns = [];
        $tiles = str_split($this->boardTiles, 2);
        $indX = 0;
        $indY = 0;
        foreach ($tiles as $tile) {
            if ($tile === self::TAVERN) {
                $this->taverns[]= new Tavern($indX, $indY);
            }
            if (++$indY % $this->boardSize === 0) {
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
        $tiles = str_split($this->boardTiles, 2);
        $indX = 0;
        $indY = 0;
        foreach ($tiles as $tile) {
            if (strpos($tile, '$') !== false) {
                $this->mines[]= new Mine($indX, $indY, $tile[1]);
            }
            if (++$indY % $this->boardSize === 0) {
                $indX++;
                $indY = 0;
            }
        }
    }

    /**
     * Update the game state, update only what may change
     *
     * TODO : store all previous states for some data as mines to detect the competition on this one
     *
     * @param array $gameData
     */
    public function update($gameData)
    {
        // update game
        $this->turn = $gameData['game']['turn'];
        $this->finished = $gameData['game']['finished'];
        // update board
        $this->boardTiles = $gameData['game']['board']['tiles'];
        $this->updateMines();
        // update enemies
        $indEnemy = 0;
        foreach ($gameData['game']['heroes'] as $playerData) {
            if ($playerData['id'] !== $gameData['hero']['id']) {
                $enemy = $this->enemies[$indEnemy];
                $enemy->update($playerData, $this->getMines());
                $indEnemy++;
            }
        }
        // update hero
        $this->hero->update($gameData['hero'], $this->getMines());
        $this->updateBoardCosts();
    }

    /**
     * Update mines
     */
    protected function updateMines()
    {
        $tiles = str_split($this->boardTiles, 2);
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
     * Update board costs
     */
    protected function updateBoardCosts()
    {
        $factory = new BoardCostsFactory();
        $this->boardCosts = $factory->create($this);
    }

    /**
     * Finish the game, can be normal end of timeout issue
     *
     * @param array $gameData
     */
    public function finish($gameData)
    {
        if (isset($gameData['error'])) {
            echo sprintf('GAME HAS BEEN ABORTED %d/%d', $this->turn, $this->maxTurns).PHP_EOL;
            echo 'ERROR: '.$gameData['error']['content'].PHP_EOL;
        } else {
            echo sprintf('GAME IS FINISHED %d/%d', $this->turn, $this->maxTurns).PHP_EOL;
            $players = $this->ranking->byGoldAmount();
            $winner = $players[0];
            if ($winner === $this->hero) {
                echo 'YOU WIN'.PHP_EOL;
            } else {
                echo 'YOU LOOSE'.PHP_EOL;
            }
            echo 'RANKING'.PHP_EOL;
            $position = 1;
            foreach ($players as $player) {
                echo sprintf(
                    '%d : %s (%d) with %d gold',
                    $position++,
                    $player->getName(),
                    $player->getId(),
                    $player->getGold()
                ).PHP_EOL;
            }
        }
    }

    /**
     * @return Hero
     */
    public function getHero()
    {
        return $this->hero;
    }

    /**
     * @return Enemy[]
     */
    public function getEnemies()
    {
        return $this->enemies;
    }

    /**
     * @return AbstractPlayer
     */
    public function getPlayers()
    {
        return $this->players;
    }

    /**
     * @return integer
     */
    public function getBoardSize()
    {
        return $this->boardSize;
    }

    /**
     * @return string
     */
    public function getBoardTiles()
    {
        return $this->boardTiles;
    }

    /**
     * @return Ranking
     */
    public function getRanking()
    {
        return $this->ranking;
    }

    /**
     * @return integer
     */
    public function getTurn()
    {
        return $this->turn;
    }

    /**
     * @return integer
     */
    public function getMaxTurns()
    {
        return $this->maxTurns;
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
     * @param Hero $hero
     *
     * @return Mine|null
     */
    public function getClosestNotOwnedMine(Hero $hero)
    {
        $notOwnedMines = [];
        foreach ($this->getMines() as $mine) {
            if ($mine->isOwnedBy($hero) === false) {
                $notOwnedMines[]= $mine;
            }
        }

        return $this->getClosestDestination($hero, $notOwnedMines);
    }

    /**
     * @param Hero $hero
     *
     * @return Tavern
     */
    public function getClosestTavern(Hero $hero)
    {
        $taverns = $this->getTaverns();

        return $this->getClosestDestination($hero, $taverns);
    }

    /**
     * @return Hero|null
     */
    public function getEnemyWithMoreMines()
    {
        // TODO : if less gold and les mine than hero avoid to attack
        // TODO : a enemy class + inject extra deps/utils in Hero (Astar?)
        // TODO : use collections with custom sorters
        $enemies = $this->getEnemies();
        $target = null;
        foreach ($enemies as $enemy) {
            if ($target === null) {
                $target = $enemy;
            } elseif ($target->getMineCount() < $enemy->getMineCount()) {
                $target = $enemy;
            }
        }

        return $target;
    }

    /**
     * @param AbstractPlayer $player
     *
     * @return boolean
     */
    public function ownsAllMines(AbstractPlayer $player)
    {
        return count($player->getOwnedMines()) == count($this->getMines());
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
                // TODO : takes cost in account
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
        $aStar = new PathFinder($this->boardCosts);
        $path = $aStar->find($start, $destination);

        if (self::DEBUG) {
            $printer = new PathPrinter($this->boardCosts, $path);
            $printer->printPath();
        }

        return $path;
    }
}
