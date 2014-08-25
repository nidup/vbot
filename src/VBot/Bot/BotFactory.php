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
        $decisionEngine = new Decision\DecisionEngine();
        $moveEngine = new Move\MoveEngine();
        $bot = new FSMBot($decisionEngine, $moveEngine);

        return $bot;
    }
}
