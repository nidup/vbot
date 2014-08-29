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
}
