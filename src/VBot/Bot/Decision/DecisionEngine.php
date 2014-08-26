<?php

namespace VBot\Bot\Decision;

use VBot\Game\Game;
use VBot\Game\DestinationInterface;
use VBot\Finite\Loader\ExpressionLoader;
use Finite\StatefulInterface;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachine;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Yaml\Parser;

/**
 * Decision engine
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class DecisionEngine implements DecisionEngineInterface, StatefulInterface
{
    /** @var boolean */
    const DEBUG = true;

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
    public function decide(Game $game)
    {
        $this->updateGame($game);
        $this->loadStateMachine();
        $target = $this->compute();

        return $target;
    }

    /**
     * @param string $state
     */
    public function setFiniteState($state)
    {
        $this->state = $state;
    }

    /**
     * @return StateInterface
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
     * @param Game $game
     */
    protected function updateGame(Game $game)
    {
        $this->game = $game;
    }

    /**
     * Initialize the state machine
     *
     * @return boolean
     */
    protected function loadStateMachine()
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
     * Compute transitions to retrieve the target
     *
     * @return DestinationInterface|null
     */
    protected function compute()
    {
        if (self::DEBUG) {
            $hero = $this->game->getHero();
            echo 'Turn:'.$this->game->getTurn().' state:'.$this->state.' life:'.$hero->getLife().' gold:'.$hero->getGold().' Pos x:y'.$hero->getPosX().':'.$hero->getPosY();
        }

        $transitions = $this->stateMachine->getCurrentState()->getTransitions();
        foreach ($transitions as $transition) {
            if ($this->stateMachine->can($transition)) {
                if (self::DEBUG) {
                    echo '>> apply '.$transition.PHP_EOL;
                }
                // apply updates the target
                $this->stateMachine->apply($transition);
                break;
            }
        }

        $currentState = $this->stateMachine->getCurrentState();
        if ($currentState->has('target')) {
            $targetExpression = $currentState->get('target');
            $language = new ExpressionLanguage();
            $target = $language->evaluate(
                $targetExpression,
                ['game' => $this->game, 'hero' => $this->game->getHero()]
            );

            if (self::DEBUG && $target) {
                echo ' Targ x:y'.$target->getPosX().':'.$target->getPosY().PHP_EOL;
            }

            return $target;
        }

        return null;
    }
}
