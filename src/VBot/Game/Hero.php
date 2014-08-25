<?php

namespace VBot\Game;

/**
 * Hero model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Hero implements DestinationInterface
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
     * @param array $heroData
     */
    public function __construct(array $heroData)
    {
        $this->id = $heroData['id'];
        $this->name = $heroData['name'];
        $this->userId = isset($heroData['userId']) ? $heroData['userId'] : 'TrainingBot';
        $this->elo = isset($heroData['elo']) ? $heroData['elo'] : 0;
        $this->life = $heroData['life'];
        $this->gold = $heroData['gold'];
        $this->mineCount = $heroData['mineCount'];
        $this->crashed = $heroData['crashed'];
        $this->position = new Position($heroData['pos']);
        $this->spawnPosition = new Position($heroData['spawnPos']);
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
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
     * @return boolean
     */
    public function isCrashed()
    {
        return $this->crashed;
    }
}
