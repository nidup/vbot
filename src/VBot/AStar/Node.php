<?php

namespace VBot\AStar;

/**
 * Node, forked from git@github.com:jmgq/php-a-star.git
 *
 * We don't use this library due to performance issues on big graphes, mainly due to
 * number of calls and use of objects, we simplied it and, unfortunately, make it less readable
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Node
{
    /** @var string */
    public $id;

    /** @var Node */
    public $parent;

    /** @var integer */
    public $gScore;

    /** @var integer */
    public $hScore;

    /** @var integer */
    public $row;

    /** @var integer */
    public $column;

    /**
     * Constructor with row and column
     */
    public function __construct($row, $column)
    {
        $this->row = $row;
        $this->column = $column;
        $this->id = $this->row.'x'.$this->column;
    }

    public function getRow()
    {
        return $this->row;
    }

    public function getColumn()
    {
        return $this->column;
    }
}
