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
                $rowCost[]= Game::MAX_COST;
            } elseif ($tile === Game::TAVERN) {
                $rowCost[]= 50;
            } elseif (strpos($tile, '$') !== false) {
                $rowCost[]= 50;
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
        $enemies = $game->getEnemies();
        $hero = $game->getHero();
        foreach ($enemies as $enemy) {
            $enemyCost = $enemy->getLife() - $hero->getLife() + 10;
            if ($enemyCost > 0) {
                // update enemy cost
                $boardCosts[$enemy->getPosX()][$enemy->getPosY()] = $enemyCost;
                // update adjacent costs
                $adjacentCost = (int) ceil($enemyCost/2);
                $adjacents = [
                    ['x' => -1, 'y' => 0],
                    ['x' => 1, 'y' => 0],
                    ['x' => 0, 'y' => -1],
                    ['x' => 0, 'y' => 1],
                ];
                foreach ($adjacents as $adjacent) {
                    $posX = $enemy->getPosX() + $adjacent['x'];
                    $posY = $enemy->getPosY() + $adjacent['y'];
                    if (isset($boardCosts[$posX][$posY]) && $boardCosts[$posX][$posY] < Game::MAX_COST) {
                        $boardCosts[$posX][$posY]= $boardCosts[$posX][$posY] + $adjacentCost;
                    }
                }
            }
        }

        return $boardCosts;
    }
}
