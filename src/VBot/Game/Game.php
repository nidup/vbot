<?php

namespace VBot\Game;

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

    /** @var Enemy[] */
    protected $enemies;

    /** @var Board */
    protected $board;

    /** @var boolean */
    protected $finished;

    /** @var Hero */
    protected $hero;

    /** @var string */
    protected $token;

    /** @var string */
    protected $viewUrl;

    /** @var string */
    protected $playUrl;

    /**
     * @param array $gameData
     */
    public function __construct(array $gameData)
    {
        $this->id = $gameData['game']['id'];
        $this->turn = $gameData['game']['turn'];
        $this->maxTurns = $gameData['game']['maxTurns'];
        $this->enemies = [];
        foreach ($gameData['game']['heroes'] as $playerData) {
            if ($playerData['id'] !== $gameData['hero']['id']) {
                $this->enemies[]= new Enemy($playerData);
            }
        }
        $this->board = new Board($gameData['game']['board']);
        $this->finished = $gameData['game']['finished'];
        $this->hero = new Hero($gameData['hero']);
        $this->token = $gameData['token'];
        $this->viewUrl = $gameData['viewUrl'];
        $this->playUrl = $gameData['playUrl'];
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
     * @return Board
     */
    public function getBoard()
    {
        return $this->board;
    }

    /**
     * @return integer
     */
    public function getTurn()
    {
        return $this->turn;
    }

    /**
     * @return Tavern[]
     */
    public function getTaverns()
    {
        return $this->board->getTaverns();
    }

    /**
     * @return Mine[]
     */
    public function getMines()
    {
        return $this->board->getMines();
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
            if ($mine->getOwnerId() !== $hero->getId()) {
                $notOwnedMines[]= $mine;
            }
        }

        return $this->board->getClosestDestination($hero, $notOwnedMines);
    }

    /**
     * @param Hero $hero
     *
     * @return Tavern
     */
    public function getClosestTavern(Hero $hero)
    {
        $taverns = $this->getTaverns();

        return $this->board->getClosestDestination($hero, $taverns);
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
}
