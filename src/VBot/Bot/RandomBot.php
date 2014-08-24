<?php

namespace VBot\Bot;

/**
 * Random Bot
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RandomBot implements BotInterface
{
    public function move($state)
    {
        $dirs = array('Stay', 'North', 'South', 'East', 'West');

        return $dirs[array_rand($dirs)];
    }
}
