<?php

namespace VBot\Bot;

/**
 * Bot Factory
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class BotFactory
{
    /**
     * @param string $type
     * @param array  $options
     *
     * @return BotInterface
     */
    public function createBot($type = 'default', $options = [])
    {
        $moveEngine = new Move\MoveEngine();
        $bot = new FSMBot($moveEngine);

        return $bot;
    }
}
