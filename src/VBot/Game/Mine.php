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

    /** @var boolean */
    protected $newOwner = false;

    /** @var string */
    const NEUTRAL = '-';

    /**
     * Create a mine
     *
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
     * Update the mine state
     *
     * @param string $ownerId
     */
    public function update($ownerId)
    {
        $ownerId = ($ownerId === self::NEUTRAL) ? null : (int) $ownerId;
        // TODO store all owners during turns
        $this->newOwner = ($this->ownerId !== $ownerId);
        $this->ownerId = $ownerId;
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
    public function hasNewOwner()
    {
        return $this->newOwner;
    }

    /**
     * @return boolean
     */
    public function isNeutral()
    {
        return $this->ownerId === null;
    }

    /**
     * @param AbstractPlayer $player
     *
     * @return boolean
     */
    public function isOwnedBy(AbstractPlayer $player)
    {
        return $this->ownerId === $player->getId();
    }
}
