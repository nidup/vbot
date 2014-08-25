<?php

namespace VBot\Game;

/**
 * Board model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Board
{
    /** @var integer */
    protected $size;

    /** @var string */
    protected $tiles;

    /**
     * @param array $boardData
     */
    public function __construct(array $boardData)
    {
        $this->size = (int) $boardData['size'];
        $this->tiles = $boardData['tiles'];
    }

    /**
     * @return string
     */
    public function getTiles()
    {
        return $this->tiles;
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }
}
