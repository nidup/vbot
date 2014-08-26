<?php

namespace VBot\AStar;

/**
 * Node, forked from git@github.com:jmgq/php-a-star.git
 * We don't use this library due to performance issues on big graphes
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Node
{
    /** @var Node */
    protected $parent;

    /** @var Node[] */
    protected $children = array();

    protected $gScore;
    protected $hScore;

    /** @var integer */
    protected $row;

    /** @var integer */
    protected $column;

    /**
     * Constructor with row and column
     */
    public function __construct($row, $column)
    {
        $this->row = $row;
        $this->column = $column;
    }

    public function setParent(Node $parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function addChild(Node $child)
    {
        $child->setParent($this);

        $this->children[] = $child;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function getF()
    {
        return $this->gScore + $this->hScore;
    }

    public function setG($score)
    {
        if (!is_numeric($score)) {
            throw new \InvalidArgumentException('The G value is not a number');
        }

        $this->gScore = $score;
    }

    public function getG()
    {
        return $this->gScore;
    }

    public function setH($score)
    {
        if (!is_numeric($score)) {
            throw new \InvalidArgumentException('The H value is not a number');
        }

        $this->hScore = $score;
    }

    public function getH()
    {
        return $this->hScore;
    }

    public function getRow()
    {
        return $this->row;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function getID()
    {
        return $this->row . 'x' . $this->column;
    }
}
