<?php

namespace VBot\Bot\Decision;

use VBot\Game\Game;
use VBot\Game\DestinationInterface;
use Finite\StatefulInterface;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachine;
use Finite\Loader\ArrayLoader;

/**
 * Decision engine
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class DecisionEngine implements DecisionEngineInterface, StatefulInterface
{
    /** @var boolean */
    const DEBUG = false;

    /** @var Game */
    protected $game;

    /** @var string $state */
    protected $state;

    /** @var StateMachine */
    protected $stateMachine = null;

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

        $this->stateMachine = new StateMachine($this);
        $data = [
            //'class' => 'FSMBot',
            'states' => [
                'stay' => [
                    'type' => StateInterface::TYPE_INITIAL,
                    'properties' => []
                ],
                /*'goto-tavern' => [
                    'type' => StateInterface::TYPE_NORMAL,
                    'properties' => []
                ],*/
                'goto-enemy' => [
                    'type' => StateInterface::TYPE_NORMAL,
                    'properties' => []
                ]
            ],
            'transitions' => [
                'waiting' => [
                    'from' => ['stay'],
                    'to' => 'goto-enemy',
                    'guard' => function () {
                        return $this->game->getTurn() >= 20;
                    }
                ],
                /*'hurted' => [
                    'from' => ['stay', 'goto-enemy'],
                    'to' => 'goto-tavern',
                    'guard' => function () {
                        $hero = $this->game->getHero();

                            return $hero->getLife() < 30 && $hero->getGold() >= 2;
                        }
                    ],
                    'healed' => [
                        'from' => ['goto-tavern'],
                        'to' => 'goto-enemy'
                    ]*/
            ]
        ];
        $loader = new ArrayLoader($data);
        $loader->load($this->stateMachine);
        $this->stateMachine->setObject($this);
        $this->stateMachine->initialize();

        return true;
    }

    /**
     * Compute transitions to get the target
     *
     * @return DestinationInterface|null
     */
    protected function compute()
    {
        $transitions = $this->stateMachine->getTransitions();
        if (self::DEBUG) {
            echo $this->state.PHP_EOL;
        }

        foreach ($transitions as $transition) {
            if (self::DEBUG) {
                echo 'check'.$transition.PHP_EOL;
            }
            if ($this->stateMachine->can($transition)) {
                if (self::DEBUG) {
                    echo 'apply'.PHP_EOL;
                }
                $this->stateMachine->apply($transition);
                break;
            }
        }

        if (self::DEBUG) {
            echo PHP_EOL.$this->state.PHP_EOL;
        }

        if ($this->state === 'waiting') {
            return null;
        }

        $target = null;
        if ($this->state === 'goto-enemy') {
            // detect biggest mine owner
            // TODO : use collections with custom sorters
            $enemies = $this->game->getEnemies();
            foreach ($enemies as $enemy) {
                if ($target === null) {
                    $target = $enemy;
                } elseif ($target->getMineCount() < $enemy->getMineCount()) {
                    $target = $enemy;
                }
            }

        } elseif ($this->state === 'goto-tavern') {
            $taverns = $this->game->getTaverns();
            $target = current($taverns);
        }

        return $target;
    }
}
