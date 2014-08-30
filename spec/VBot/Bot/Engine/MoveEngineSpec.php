<?php

namespace spec\VBot\Bot\Engine;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use VBot\Game\Game;
use VBot\Game\Board;
use VBot\Game\Hero;
use VBot\Game\DestinationInterface;
use VBot\AStar\Node;

class MoveEngineSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('VBot\Bot\Engine\MoveEngine');
    }

    function it_chooses_to_stay_when_the_hero_has_no_target(Game $game, Hero $hero)
    {
        $game->getHero()->willReturn($hero);
        $hero->getTarget()->willReturn(null);
        $hero->setDirection('Stay')->shouldBeCalled();
        $this->process($game);
    }

    function it_chooses_to_stay_when_the_hero_has_no_path_to_the_target(Game $game, Board $board, Hero $hero, DestinationInterface $target)
    {
        $game->getHero()->willReturn($hero);
        $hero->getTarget()->willReturn($target);
        $game->getBoard()->willReturn($board);
        $board->getShortestPath($hero, $target)->willReturn([]);
        $hero->setDirection('Stay')->shouldBeCalled();
        $this->process($game);
    }

    function it_chooses_to_move_when_the_hero_has_a_path_to_the_target(Game $game, Board $board, Hero $hero, DestinationInterface $target)
    {
        $game->getHero()->willReturn($hero);
        $hero->getTarget()->willReturn($target);
        $game->getBoard()->willReturn($board);
        $firstNode = new Node(3, 1);
        $path = [null, $firstNode];
        $board->getShortestPath($hero, $target)->willReturn($path);
        $hero->getPosX()->willReturn(2);
        $hero->getPosY()->willReturn(1);
        $hero->setDirection('South')->shouldBeCalled();
        $this->process($game);
    }
}
