<?php

namespace VBot\AStar;

use VBot\Game\Board;

/**
 * Create TerrainCost from Vindinium board tiles
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class TerrainCostFactory
{
    /** @varstatic string */
    const IMPASSABLE_WOOD = '##';

    /** @varstatic string */
    const TAVERN = '[]';

    /** @varstatic string */
    const GOLD_MINE = '$%';

    /** @varstatic string */
    const HERO = '@%';

    /**
     * @param Board $board
     *
     * @return TerrainCost
     */
    public function create($board)
    {
        $size = $board->getSize();
        $tiles = str_split($board->getTiles(), 2);
        $indX = 0;
        $cost = [];
        $rowCost = [];
        foreach ($tiles as $tile) {
            $rowCost[]= ($tile === self::IMPASSABLE_WOOD) ? PHP_INT_MAX : 1;
            if (++$indX % $size === 0) {
                $cost[]= $rowCost;
                $rowCost = [];
            }
        }

        return new TerrainCost($cost);
    }
}
