<?php

namespace VBot\Bot\Decision;

use VBot\Game\Game;
use VBot\Game\DestinationInterface;
use VBot\Finite\Loader\ExpressionLoader;
use Finite\StatefulInterface;
use Finite\State\State;
use Finite\State\StateInterface;
use Finite\StateMachine\StateMachine;

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

    /** @var StateMachine $stateMachine */
    protected $stateMachine = null;

    /** @var DestinationInterface $target */
    protected $target = null;

    /**
     * {@inheritDoc}
     */
    public function decide(Game $game)
    {
        $this->updateGame($game);
        $this->loadStateMachine();
        $this->compute();

        return $this->target;
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
     * @param DestinationInterface|null
     */
    public function setTarget($destination)
    {
        $this->target = $destination;
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
                'goto-mine' => [
                    'type' => StateInterface::TYPE_NORMAL,
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
                'dead' => [
                    'type' => StateInterface::TYPE_FINAL,
                    'properties' => []
                ]
            ],
            'transitions' => [
                'waiting' => [
                    'from' => ['stay'],
                    'to' => 'goto-mine',
                    'expression' => [
                        'condition' => 'true',
                        'action' => 'game.getClosestNotOwnedMine(hero)'
                    ],
                ],
                'defend-mine' => [
                    'from' => ['goto-mine'],
                    'to' => 'goto-enemy',
                    'expression' => [
                        'condition' => 'hero.getMineCount() > 0',
                        'action' => 'game.getEnemyWithMoreMines()'
                    ],
                ],
                'hurted' => [
                    'from' => ['stay', 'goto-enemy', 'goto-mine'],
                    'to' => 'goto-tavern',
                    'expression' => [
                        'condition' => 'hero.getLife() < 50 && hero.getGold() >= 2',
                        'action' => 'game.getClosestTavern(hero)'
                    ],
                ],
                'healed' => [
                    'from' => ['goto-tavern'],
                    'to' => 'goto-enemy',
                    'expression' => [
                        'condition'   => 'hero.getLife() > 85',
                        'action' => 'game.getEnemyWithMoreMines()'
                    ],
                ],
                'dying' => [
                    'from' => ['stay', 'goto-mine', 'goto-tavern', 'goto-enemy'],
                    'to' => 'dead',
                    'expression' => [
                        'condition'   => 'hero.isCrashed()',
                        'action' => 'null'
                    ]
                ]
            ]
        ];
        $loader = new ExpressionLoader($data);
        $loader->load($this->stateMachine);
        $this->stateMachine->setObject($this);
        $this->stateMachine->initialize();

        return true;
    }

    /**
     * Compute transitions to update the target
     *
     * @return null
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
                // apply updates the target
                $this->stateMachine->apply($transition);
                break;
            }
        }
    }
}
