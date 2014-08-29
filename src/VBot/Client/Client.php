<?php

namespace VBot\Client;

use VBot\Bot\BotInterface;
use VBot\Game\Game;

/**
 * cf kcampion/vindinium-starter-php.git
 */
class Client
{
    const TIMEOUT = 15;
    private $bot;
    private $key;
    private $mode;
    private $numberOfGames;
    private $numberOfTurns;
    private $map;
    private $serverUrl = 'http://vindinium.org';

    public function __construct(BotInterface $bot, $key, $mode = 'training', $nbTurns = 300, $nbGames = 1, $map = 'm1')
    {
        $this->bot = $bot;
        $this->key = $key;
        $this->mode = $mode;
        $this->numberOfTurns = $nbTurns;
        $this->numberOfGames = $nbGames;
        $this->map = $map;
    }

    public function load()
    {
        for ($i = 0; $i <= ($this->numberOfGames - 1); $i++) {
            $this->start($this->bot);
            echo "\nGame finished: " . ($i + 1) . "/" . $this->numberOfGames . "\n";
        }
    }

    private function start($botObject)
    {
        // Starts a game with all the required parameters
        if ($this->mode == 'arena') {
            echo "Connected and waiting for other players to join...\n";
        }

        // Get the initial state
        $state = $this->getNewGameState();
        $game = new Game($state);
        echo "Playing at: " . $state['viewUrl'] . "\n";

        ob_start();
        while ($this->isFinished($state) === false) {
            // Some nice output ;)
            echo '.';
            ob_flush();
            // Move to some direction
            $url = $state['playUrl'];
            $direction = $botObject->move($game);
            $state = $this->move($url, $direction);
            // potential timeout
            if ($this->isFinished($state) === false) {
                $game->update($state);
            }
        }
        $game->finish($state);

        ob_flush();
        ob_end_clean();
    }

    private function getNewGameState()
    {
        // Get a JSON from the server containing the current state of the game
        if ($this->mode == 'training') {
            // Don't pass the 'map' parameter if you want a random map
            $params = array('key' => $this->key, 'turns' => $this->numberOfTurns, 'map' => $this->map);
            $api_endpoint = '/api/training';
        } elseif ($this->mode == 'arena') {
            $params = array('key' => $this->key);
            $api_endpoint = '/api/arena';
        }

        // Wait for 10 minutes
        $r = HttpPost::post($this->serverUrl . $api_endpoint, $params, 10 * 60);

        if (isset($r['headers']['status_code']) && $r['headers']['status_code'] == 200) {
            return json_decode($r['content'], true);
        } else {
            echo "Error when creating the game\n";
            echo $r['content'];
        }
    }

    private function move($url, $direction)
    {
        /*
         * Send a move to the server
         * Moves can be one of: 'Stay', 'North', 'South', 'East', 'West'
         */
        try {
            $r = HttpPost::post($url, array('dir' => $direction), self::TIMEOUT);
            if (isset($r['headers']['status_code']) && $r['headers']['status_code'] == 200) {
                return json_decode($r['content'], true);

            } else {
                echo "Error HTTP " . $r['headers']['status_code'] . "\n" . $r['content'] . "\n";
                $code = $r['headers']['status_code'];
                $content = $r['content'];

                return array('game' => array('finished' => true), 'error' => ['code' => $code, 'content' => $content]);
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";

            return array('game' => array('finished' => true), 'error' => ['code' => 'exception', 'content' => $e->getMessage()]);

        }
    }

    private function isFinished($state)
    {
        return $state['game']['finished'];
    }
}
