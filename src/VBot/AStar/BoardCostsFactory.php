<?php

namespace VBot\AStar;

use VBot\Game\Game;

/**
 * Costs factory, creates an array which represents difficulty/risk to cross from a node to another
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class BoardCostsFactory
{
    /**
     * @param Game $game
     *
     * @return integer[]
     */
    public function create(Game $game)
    {
        $boardCosts = $this->initializeWithGroundCosts($game);
        $boardCosts = $this->updateWithRiskCosts($game, $boardCosts);

        return $boardCosts;
    }

    /**
     * @param Game $game
     *
     * @return array
     */
    protected function initializeWithGroundCosts(Game $game)
    {
        $tiles = $game->getBoardTiles();
        $tiles = str_split($tiles, 2);
        $size = $game->getBoardSize();
        $indY = 0;
        $boardCosts = [];
        $rowCost = [];
        foreach ($tiles as $tile) {
            if ($tile === Game::IMPASSABLE_WOOD) {
                $rowCost[]= PHP_INT_MAX;
            } elseif ($tile === Game::TAVERN) {
                $rowCost[]= 50;
            } elseif (strpos($tile, '$') !== false) {
                $rowCost[]= 50;
            } elseif (strpos($tile, '@') !== false) {
                $rowCost[]= 10;
            } else {
                $rowCost[]= 1;
            }
            if (++$indY % $size === 0) {
                $indY = 0;
                $boardCosts[]= $rowCost;
                $rowCost = [];
            }
        }

        return $boardCosts;
    }

    /**
     * @param Game  $game
     * @param array $boardCosts
     *
     * @return array
     */
    protected function updateWithRiskCosts(Game $game, $boardCosts)
    {
        // TODO compute risk costs
        return $boardCosts;
    }
}
