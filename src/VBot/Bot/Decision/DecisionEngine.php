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
                    'properties' => [
                        'target' => 'game.getClosestNotOwnedMine(hero)'
                    ]
                ],
                'goto-enemy' => [
                    'type' => StateInterface::TYPE_NORMAL,
                    'properties' => [
                        'target' => 'game.getEnemyWithMoreMines()'
                    ]
                ],
                'goto-tavern' => [
                    'type' => StateInterface::TYPE_NORMAL,
                    'properties' => [
                        'target' => 'game.getClosestTavern(hero)'
                    ]
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
                    'condition' => 'true',
                ],
                'defend-mine' => [
                    'from' => ['goto-mine'],
                    'to' => 'goto-enemy',
                    'condition' => 'hero.getMineCount() > 1',
                ],
                'hurted' => [
                    'from' => ['stay', 'goto-enemy', 'goto-mine'],
                    'to' => 'goto-tavern',
                    'condition' => 'hero.getLife() < 50 && hero.getGold() >= 2',
                ],
                'healed' => [
                    'from' => ['goto-tavern'],
                    'to' => 'goto-enemy',
                    'condition' => 'hero.getLife() > 85',
                ],
                'dying' => [
                    'from' => ['stay', 'goto-mine', 'goto-tavern', 'goto-enemy'],
                    'to' => 'dead',
                    'condition' => 'hero.isCrashed()',
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
