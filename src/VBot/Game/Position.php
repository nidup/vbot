<?php

namespace VBot\Game;

/**
 * Position model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Position
{
    /** @var integer */
    protected $x;

    /** @var integer */
    protected $y;

    /**
     * @param array $positionData
     */
    public function __construct(array $positionData)
    {
        $this->x = (int) $positionData['x'];
        $this->y = (int) $positionData['y'];
    }

    /**
     * @return integer
     */
    public function getX()
    {
        return $this->x;
    }

    /**
     * @return integer
     */
    public function getY()
    {
        return $this->y;
    }
}
