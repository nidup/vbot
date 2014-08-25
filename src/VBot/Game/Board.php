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

    /** @var Tavern[] */
    protected $taverns = null;

    /** @varstatic string */
    // TODO : avoid to duplicate constants
    const TAVERN = '[]';

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

    public function getTaverns()
    {
        if ($this->taverns === null) {
            $this->taverns = [];
            $tiles = str_split($this->tiles, 2);
            $indX = 0;
            $indY = 0;
            foreach ($tiles as $tile) {
                if ($tile === self::TAVERN) {
                    $this->taverns[]= new Tavern($indX, $indY);
                }
                if (++$indY % $this->size === 0) {
                    $indX++;
                    $indY = 0;
                }
            }
        }

        return $this->taverns;
    }
}
