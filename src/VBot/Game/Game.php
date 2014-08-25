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

    /** @var Hero[] */
    protected $heroes;

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

    /** @var Hero[] */
    protected $enemies = null;

    /**
     * @param array $gameData
     */
    public function __construct(array $gameData)
    {
        $this->id = $gameData['game']['id'];
        $this->turn = $gameData['game']['turn'];
        $this->maxTurns = $gameData['game']['maxTurns'];
        $this->heroes = [];
        foreach ($gameData['game']['heroes'] as $heroData) {
            $this->heroes[]= new Hero($heroData);
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
     * @return Hero[]
     */
    public function getHeroes()
    {
        return $this->heroes;
    }

    /**
     * @return Hero[]
     */
    public function getEnemies()
    {
        if ($this->enemies === null) {
            foreach ($this->heroes as $hero) {
                if ($hero->getId() !== $this->hero->getId()) {
                    $this->enemies[]= $hero;
                }
            }
        }

        return $this->enemies;
    }

    /**
     * @return Board
     */
    public function getBoard()
    {
        return $this->board;
    }
}
