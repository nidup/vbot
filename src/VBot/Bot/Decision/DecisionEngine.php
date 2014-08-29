<?php

namespace VBot\Bot\Decision;

use VBot\Game\Game;
use VBot\Finite\Loader\ExpressionLoader;
use Finite\StatefulInterface;
use Finite\State\State;
use Finite\StateMachine\StateMachine;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Parser;

/**
 * Decision engine
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class DecisionEngine implements DecisionEngineInterface, StatefulInterface
{
    /** @var array */
    protected $options;

    /** @var Game */
    protected $game;

    /** @var string $state */
    protected $state;

    /** @var StateMachine $stateMachine */
    protected $stateMachine = null;

    /**
     * @param array $options
     */
    public function __construct($options)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['fsm_path']);
        $this->options = $resolver->resolve($options);
    }

    /**
     * {@inheritDoc}
     */
    public function process(Game $game)
    {
        $this->initializeGame($game);
        $this->initializeStateMachine();
        $this->compute();
    }

    /**
     * {@inheritDoc}
     */
    public function setFiniteState($state)
    {
        $this->state = $state;
    }

    /**
     * {@inheritDoc}
     */
    public function getFiniteState()
    {
        return $this->state;
    }

    /**
     * @return Game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * Initialize the game
     *
     * @param Game $game
     *
     * @return boolean
     */
    protected function initializeGame($game)
    {
        if ($this->game !== null) {
            return false;
        }
        $this->game = $game;

        return true;
    }

    /**
     * Initialize the state machine
     *
     * @return boolean
     */
    protected function initializeStateMachine()
    {
        if ($this->stateMachine !== null) {
            return false;
        }

        $yaml = new Parser();
        $data = $yaml->parse(file_get_contents($this->options['fsm_path']));
        $loader = new ExpressionLoader($data);

        $this->stateMachine = new StateMachine($this);
        $loader->load($this->stateMachine);
        $this->stateMachine->setObject($this);
        $this->stateMachine->initialize();

        return true;
    }

    /**
     * Compute transitions to update the target
     *
     * @return boolean
     */
    protected function compute()
    {
        $transitions = $this->stateMachine->getCurrentState()->getTransitions();
        foreach ($transitions as $transition) {
            if ($this->stateMachine->can($transition)) {
                echo 'Apply '.$transition.PHP_EOL;
                $this->stateMachine->apply($transition);
                break;
            }
        }

        return true;
    }
}
