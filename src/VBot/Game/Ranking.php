<?php

namespace VBot\Game;

/**
 * Ranking model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Ranking
{
    /** @var AbstractPlayer[] */
    protected $players;

    /**
     * @param AbstractPlayer[] $players
     */
    public function __construct($players)
    {
        $this->players = $players;
    }

    /**
     * @return array
     */
    public function byGoldAmount()
    {
        usort(
            $this->players,
            function ($playerOne, $playerTwo) {
                return $playerOne->getGold() < $playerTwo->getGold();
            }
        );

        return $this->players;
    }

    /**
     * @return array
     */
    public function byOwnedMines()
    {
        usort(
            $this->players,
            function ($playerOne, $playerTwo) {
                return count($playerOne->getOwnedMines()) < count($playerTwo->getOwnedMines());
            }
        );

        return $this->players;
    }

    /**
     * @param AbstractPlayer $player
     *
     * @return boolean
     */
    public function isLeader(AbstractPlayer $player)
    {
        // TODO : takes only gold in account and introduce a predictive willWin method ?
        $this->byGoldAmount();
        $hasMoreGold = $this->players[0] === $player;
        $this->byOwnedMines();
        $hasMoreMines = $this->players[0] === $player;
        $isLeader = $hasMoreGold && $hasMoreMines;

        return $isLeader;
    }
}
