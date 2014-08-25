<?php

namespace VBot\Finite\Loader;

use Finite\Loader\ArrayLoader;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachineInterface;
use Finite\Event\TransitionEvent;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Expression loader, extends ArrayLoader, would prefer decorate it but the whole content is private,
 * no way to add options in transitions config too
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ExpressionLoader extends ArrayLoader
{
    /** @var array */
    protected $config;

    /**
     * @param array           $config
     * @param CallbackHandler $handler
     */
    public function __construct(array $config, CallbackHandler $handler = null)
    {
        parent::__construct($config, $handler);
        $this->callbackHandler = $handler;
        $this->config = array_merge(
            array(
                'class'       => '',
                'states'      => [],
                'transitions' => [],
                'expressions' => []
            ),
            $config
        );
    }

    /**
     * {@inheritDoc}
     */
    public function load(StateMachineInterface $stateMachine)
    {
        parent::load($stateMachine);
        // TODO would prefer add expression inside transition, needs to rewrite the loader
        $this->loadExpressions($stateMachine);
    }

    /**
     * @{inheritDoc}
     */
    public function supports(StatefulInterface $object)
    {
        return parent::supports($object);
    }

    /**
     * @param StateMachineInterface $stateMachine
     */
    protected function loadExpressions($stateMachine)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['action', 'condition']);
        $language = new ExpressionLanguage();

        foreach ($this->config['expressions'] as $transition => $config) {
            $config = $resolver->resolve($config);
            $stateMachine->getDispatcher()->addListener(
                'finite.test_transition.'.$transition,
                function (TransitionEvent $event) use ($language, $config) {
                    $hero = $event->getStateMachine()->getObject()->getGame()->getHero();
                    if ($language->evaluate($config['condition'], ['hero' => $hero]) === false) {
                        $event->reject();
                    } else {
                        // TODO : execute action
                    }
                }
            );
        }
    }
}
