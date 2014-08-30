<?php

namespace spec\VBot\Game;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class GameSpec extends ObjectBehavior
{
    function let(){
        $gameData = [
            'game' => [
                'id'   => 'id123',
                'turn' => 4,
                'maxTurns' => 12,
                'finished' => false,
                'heroes' => [
                    [
                        'id' => 1,
                        'name' => 'My name',
                        'userId' => 'MyId',
                        'elo' => 1200,
                        'life' => 100,
                        'gold' => 0,
                        'mineCount' => 0,
                        'crashed' => false,
                        'pos' => ['x' => 1, 'y' => 2],
                        'spawnPos' => ['x' => 1, 'y' => 2],
                    ]
                ],
                'board' => [
                    'size' => 4,
                    'tiles' => '##  @1  ##      ##      ##      '
                ],
            ],
            'hero' => [
                'id' => 1,
                'name' => 'My name',
                'userId' => 'MyId',
                'elo' => 1200,
                'life' => 100,
                'gold' => 0,
                'mineCount' => 0,
                'crashed' => false,
                'pos' => ['x' => 1, 'y' => 2],
                'spawnPos' => ['x' => 1, 'y' => 2],
            ],
            'token' => 't123',
            'viewUrl' => 'http://view',
            'playUrl' => 'http://play'
        ];
        $this->beConstructedWith($gameData);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('VBot\Game\Game');
    }
}
