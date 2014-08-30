<?php

namespace VBot\Bot;

use Symfony\Component\OptionsResolver\OptionsResolver;
use VBot\Bot\Engine\DecisionEngine;
use VBot\Bot\Engine\MoveEngine;

/**
 * Bot Factory
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class BotFactory
{
    /**
     * @param array $options the bot config
     *
     * @return BotInterface
     */
    public function createBot($options = [])
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['decision']);
        $options = $resolver->resolve($options);

        $decisionEngine = new DecisionEngine($options['decision']);
        $moveEngine = new MoveEngine();
        $bot = new FSMBot($decisionEngine, $moveEngine);

        return $bot;
    }
}
