<?php

namespace VBot\Game;

/**
 * Abstract player model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
abstract class AbstractPlayer implements DestinationInterface
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $userId;

    /** @var integer */
    protected $elo;

    /** @var integer */
    protected $life;

    /** @var integer */
    protected $gold;

    /** @var integer */
    protected $mineCount;

    /** @var boolean */
    protected $crashed;

    /** @var Position */
    protected $position;

    /** @var Position */
    protected $spawnPosition;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->userId = isset($data['userId']) ? $data['userId'] : '__TrainingBot';
        $this->elo = isset($data['elo']) ? $data['elo'] : 0;
        $this->life = $data['life'];
        $this->gold = $data['gold'];
        $this->mineCount = $data['mineCount'];
        $this->crashed = $data['crashed'];
        $this->position = new Position($data['pos']);
        $this->spawnPosition = new Position($data['spawnPos']);
    }

    /**
     * Update the player state, update only what may change
     *
     * TODO : store all previous states for some data, for instance, nb dies, etc
     *
     * @param array $gameData
     */
    public function update($data)
    {
        $this->life = $data['life'];
        $this->gold = $data['gold'];
        $this->mineCount = $data['mineCount'];
        $this->crashed = $data['crashed'];
        $this->position = new Position($data['pos']);
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Position
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return Position
     */
    public function getSpawnPosition()
    {
        return $this->spawnPosition;
    }

    /**
     * @return integer
     */
    public function getPosX()
    {
        return $this->position->getX();
    }

    /**
     * @return integer
     */
    public function getPosY()
    {
        return $this->position->getY();
    }

    /**
     * @return integer
     */
    public function getMineCount()
    {
        return $this->mineCount;
    }

    /**
     * @return integer
     */
    public function getGold()
    {
        return $this->gold;
    }

    /**
     * @return integer
     */
    public function getLife()
    {
        return $this->life;
    }

    /**
     * @return integer
     */
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * @return boolean
     */
    public function isCrashed()
    {
        return $this->crashed;
    }
}
