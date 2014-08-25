<?php

namespace VBot\Finite\Loader;

use Finite\Loader\LoaderInterface;
use Finite\StatefulInterface;
use Finite\StateMachine\StateMachineInterface;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\Transition\Transition;
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
class ExpressionLoader implements LoaderInterface
{
    /** @var array */
    protected $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = array_merge(
            array(
                'class'       => '',
                'states'      => [],
                'transitions' => []
            ),
            $config
        );
    }

    /**
     * @{inheritDoc}
     */
    public function load(StateMachineInterface $stateMachine)
    {
        $this->loadStates($stateMachine);
        $this->loadTransitions($stateMachine);
    }

    /**
     * @{inheritDoc}
     */
    public function supports(StatefulInterface $object)
    {
        $reflection = new \ReflectionClass($this->config['class']);

        return $reflection->isInstance($object);
    }

    /**
     * @param StateMachineInterface $stateMachine
     */
    protected function loadStates(StateMachineInterface $stateMachine)
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults(array('type' => StateInterface::TYPE_NORMAL, 'properties' => array()));
        $resolver->setAllowedValues(
            array(
                'type' => array(
                    StateInterface::TYPE_INITIAL,
                    StateInterface::TYPE_NORMAL,
                    StateInterface::TYPE_FINAL
                )
            )
        );

        foreach ($this->config['states'] as $state => $config) {
            $config = $resolver->resolve($config);
            $stateMachine->addState(new State($state, $config['type'], array(), $config['properties']));
        }
    }

    /**
     * @param StateMachineInterface $stateMachine
     */
    protected function loadTransitions(StateMachineInterface $stateMachine)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['from', 'to', 'expression']);
        $resolver->setNormalizers(['from' => function (Options $options, $v) { return (array) $v; }]);

        foreach ($this->config['transitions'] as $transition => $config) {
            $config = $resolver->resolve($config);
            $stateMachine->addTransition(new Transition($transition, $config['from'], $config['to']));
            $this->addTransitionListener($stateMachine, $transition, $config['expression']);
        }
    }

    /**
     * @param StateMachineInterface $stateMachine
     * @param string                $transition
     * @param array                 $config
     */
    protected function addTransitionListener(StateMachineInterface $stateMachine, $transition, $config)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['action', 'condition']);
        $config = $resolver->resolve($config);

        // TODO same instance of EL ?
        $language = new ExpressionLanguage();
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
