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
    const DEBUG = true;

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
                'goto-enemy' => [
                    'type' => StateInterface::TYPE_NORMAL,
                    'properties' => []
                ],
                'goto-tavern' => [
                    'type' => StateInterface::TYPE_NORMAL,
                    'properties' => []
                ],
            ],
            'transitions' => [
                'waiting' => [
                    'from' => ['stay'],
                    'to' => 'goto-enemy',
                    'guard' => function () {
                        return $this->game->getTurn() >= 20;
                    }
                ],
                'hurted' => [
                    'from' => ['stay', 'goto-enemy'],
                    'to' => 'goto-tavern',
                    'guard' => function () {
                        $hero = $this->game->getHero();

                        return $hero->getLife() < 70 && $hero->getGold() >= 2;
                    }
                ],
                'healed' => [
                    'from' => ['goto-tavern'],
                    'to' => 'goto-enemy',
                    'guard' => function () {
                        $hero = $this->game->getHero();

                        return $hero->getLife() > 85;
                    }
                ]
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
        if (self::DEBUG) {
            $hero = $this->game->getHero();
            echo 'Turn:'.$this->game->getTurn().' state:'.$this->state.' life:'.$hero->getLife().' gold:'.$hero->getGold().PHP_EOL;
        }

        $transitions = $this->stateMachine->getCurrentState()->getTransitions();
        foreach ($transitions as $transition) {
            if ($this->stateMachine->can($transition)) {
                if (self::DEBUG) {
                    echo '>> apply '.$transition.PHP_EOL;
                }
                $this->stateMachine->apply($transition);
                break;
            }
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
            if (self::DEBUG) {
                echo 'tavern:'.$target->getPosX().':'.$target->getPosY().' hero:'.$hero->getPosX().':'.$hero->getPosY().PHP_EOL;
            }
        }

        return $target;
    }
}
