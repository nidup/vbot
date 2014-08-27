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
        $this->board = new Board($gameData['game']['board']);
        $this->hero = new Hero($gameData['hero']);
        $this->token = $gameData['token'];
        $this->viewUrl = $gameData['viewUrl'];
        $this->playUrl = $gameData['playUrl'];
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
        // update enemies
        $indEnemy = 0;
        foreach ($gameData['game']['heroes'] as $playerData) {
            if ($playerData['id'] !== $gameData['hero']['id']) {
                $enemy = $this->enemies[$indEnemy];
                $enemy->update($playerData);
                $indEnemy++;
            }
        }
        // update hero
        $this->hero->update($gameData['hero']);
        // update board
        $this->board->update($gameData['game']['board']);
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
