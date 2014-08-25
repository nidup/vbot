<?php

namespace VBot\Game;

/**
 * Mine model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Mine implements DestinationInterface
{
    /** @var Position */
    protected $position;

    /** @var integer */
    protected $ownerId;

    /** @var string */
    const NEUTRAL = '-';

    /**
     * @param integer $x
     * @param integer $y
     * @param string  $ownerId
     */
    public function __construct($x, $y, $ownerId)
    {
        $this->position = new Position(['x' => $x, 'y' => $y]);
        $this->ownerId = ($ownerId === self::NEUTRAL) ? null : (int) $ownerId;
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
     * @return integer|null
     */
    public function getOwnerId()
    {
        return $this->ownerId;
    }

    /**
     * @return boolean
     */
    public function isNeutral()
    {
        return $this->ownerId === null;
    }
}
