<?php

namespace VBot\Bot;

use VBot\Game\Game;

/**
 * Random Bot
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RandomBot implements BotInterface
{
    /**
     * {@inheritDoc}
     */
    public function move(Game $game)
    {
        $dirs = array('Stay', 'North', 'South', 'East', 'West');

        return $dirs[array_rand($dirs)];
    }
}
