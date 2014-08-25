<?php

namespace VBot\Game;

/**
 * Tavern model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Tavern
{
    /** @var Position */
    protected $position;

    /**
     * @param integer $x
     * @param integer $y
     */
    public function __construct($x, $y)
    {
        $this->position = new Position(['x' => $x, 'y' => $y]);
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
}
