<?php

namespace VBot\Game;

/**
 * Ranking model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Ranking
{
    /** @var Game */
    protected $game;

    /**
     * @param Game $game
     */
    public function __construct($game)
    {
        $this->game = $game;
    }

    /**
     * @return array
     */
    public function byGoldAmount()
    {
        $players = $this->game->getPlayers();
        usort(
            $players,
            function ($playerOne, $playerTwo) {
                return $playerOne->getGold() < $playerTwo->getGold();
            }
        );

        return $players;
    }

    /**
     * @return array
     */
    public function byOwnedMines()
    {
        $players = $this->game->getPlayers();
        usort(
            $players,
            function ($playerOne, $playerTwo) {
                return count($playerOne->getOwnedMines()) < count($playerTwo->getOwnedMines());
            }
        );

        return $players;
    }

    /**
     * @param AbstractPlayer $player
     *
     * @return boolean
     */
    public function isLeader(AbstractPlayer $player)
    {
        if ($player->getGold() === 0) {
            return false;
        }
        $players = $this->byGoldAmount();
        $hasMoreGold = $players[0] === $player;

        return $hasMoreGold;
    }

    /**
     * Very first/naive implem of a predictible fuzzy ratio, 0: sure to loose, 100 : sure to win
     *
     * @param AbstractPlayer $ratioPlayer
     *
     * @return integer
     */
    public function getVictoryRatio(AbstractPlayer $currentPlayer)
    {
        $ratio = 50;
        // prepare expected data
        $players = $this->game->getPlayers();
        $remainingTurns = ($this->game->getMaxTurns() - $this->game->getTurn()) / 4;
        $expectedGains = [];
        foreach ($players as $player) {
            // add 2 golds per mine, remove thirst HP, 1 per turn
            $expectedGain =  $player->getGold() + ($player->getMineCount() * 2 * $remainingTurns) - $remainingTurns;
            // compute a not possible max gain as if a user could gets directly all mines
            $maxGain = $player->getGold() + (count($this->game->getMines()) * 2 * $remainingTurns) - $remainingTurns;
            $expectedGains[$player->getId()]['current'] = $player->getGold();
            $expectedGains[$player->getId()]['realist'] = ($expectedGain > 0) ? $expectedGain : 0;
            $expectedGains[$player->getId()]['max'] = ($maxGain > 0) ? $maxGain : 0;
            $expectedGains[$player->getId()]['player_id'] = $player->getId();
        }
        // sort players by expected gains
        $expectedRanking = $expectedGains;
        usort(
            $expectedRanking,
            function ($playerOne, $playerTwo) {
                return count($playerOne['realist']) < count($playerTwo['realist']);
            }
        );
        // player is the leader
        $leader = $expectedRanking[0];
        $firstChallenger = $expectedRanking[1];
        if ($currentPlayer->getId() === $leader['player_id']) {
            if ($leader['current'] > $firstChallenger['max']) {
                $ratio = 99;
            } elseif ($leader['realist'] > (4 * $firstChallenger['realist'])) {
                $ratio = 90;
            } elseif ($leader['realist'] > (3 * $firstChallenger['realist'])) {
                $ratio = 80;
            } elseif ($leader['realist'] > (2 * $firstChallenger['realist'])) {
                $ratio = 70;
            } else {
                $ratio = 60;
            }
        // player is a challenger
        } else {
            $challenger = current(
                array_filter(
                    $expectedRanking,
                    function ($rankingData) {
                        return $rankingData['player_id'] === $currentPlayer->getId();
                    }
                )
            );
            if ($challenger['max'] < $leader['current']) {
                $ratio = 1;
            } elseif ($challenger['realist'] < (2 * $leader['realist'])) {
                $ratio = 50;
            } elseif ($challenger['realist'] < (3 * $leader['realist'])) {
                $ratio = 40;
            } elseif ($challenger['realist'] < (4 * $leader['realist'])) {
                $ratio = 30;
            } else {
                $ratio = 20;
            }
        }
        // apply precision penality on early game
        if ($remainingTurns / $this->game->getMaxTurns() * 100 > 75) {
            $ratio -= 15;
        } elseif ($remainingTurns / $this->game->getMaxTurns() * 100 > 50) {
            $ratio -= 10;
        } elseif ($remainingTurns / $this->game->getMaxTurns() * 100 > 25) {
            $ratio -= 5;
        }
        // apply penality on number of mines owned, only one is quite dangerous ?
        /*
        $ownedMinesRatio = count($currentPlayer->getOwnedMines()) / count($this->game->getMines()) * 100;
        if ($ownedMinesRatio < 25) {
            $ratio -= 10;
        }*/

        return $ratio;
    }
}
