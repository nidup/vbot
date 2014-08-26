<?php

namespace VBot\Bot;

use Symfony\Component\OptionsResolver\OptionsResolver;

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

        $decisionEngine = new Decision\DecisionEngine($options['decision']);
        $moveEngine = new Move\MoveEngine();
        $bot = new FSMBot($decisionEngine, $moveEngine);

        return $bot;
    }
}
