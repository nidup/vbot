<?php

namespace VBot\Bot;

use Finite\StatefulInterface;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachine;
use Finite\Loader\ArrayLoader;
use VBot\Game\Game;

/**
 * Finite State Machine Bot
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class FSMBot implements BotInterface, StatefulInterface
{
    /** @var MoveEngineInterface */
    protected $moveEngine;

    /** @var StateInterface $state */
    protected $state;

    /** @var StateMachine */
    protected $stateMachine = null;

    protected $game;

    /**
     * @param MoveEngineInterface $moveEngine
     */
    public function __construct($moveEngine)
    {
        $this->moveEngine = $moveEngine;
    }

    /**
     * @param StateInterface $state
     */
    public function setFiniteState($state)
    {
        //var_dump($state);
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
     * {@inheritDoc}
     */
    public function move(Game $game)
    {
        $debug = false;

        // TODO : move logic to dedicated behavior classes with strategy pattern
        $this->game = $game;
        $stateMachine = $this->prepareStateMachine();
        $transitions = $stateMachine->getTransitions();
        if ($debug) {
            echo $this->state.PHP_EOL;
        }

        foreach ($transitions as $transition) {
            if ($debug) {
                echo 'check'.$transition.PHP_EOL;
            }
            if ($stateMachine->can($transition)) {
                if ($debug) {
                    echo 'apply'.PHP_EOL;
                }
                $stateMachine->apply($transition);
                break;
            }
        }

        if ($debug) {
            echo PHP_EOL.$this->state.PHP_EOL;
        }

        if ($this->state === 'waiting') {
            return 'Stay';
        }

        $target = null;
        if ($this->state === 'goto-enemy') {
            // detect biggest mine owner
            // TODO : use collections with custom sorters
            $enemies = $game->getEnemies();
            if (empty($enemies)) {
                return 'Stay';
            }

            foreach ($enemies as $enemy) {
                if ($target === null) {
                    $target = $enemy;
                } elseif ($target->getMineCount() < $enemy->getMineCount()) {
                    $target = $enemy;
                }
            }

        } elseif ($this->state === 'goto-tavern') {
            $taverns = $game->getTaverns();
            $target = current($taverns);
        }

        if ($target !== null) {
            return $this->moveEngine->move($game->getBoard(), $game->getHero(), $target);

            return $destination;
        }

        return 'Stay';
    }

    /**
     * @return StateMachine
     */
    protected function prepareStateMachine()
    {
        if ($this->stateMachine === null) {
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
                    ]
                    /*,
                    'hurted' => [
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
        }

        return $this->stateMachine;
    }
}
