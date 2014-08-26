<?php

namespace VBot\AStar;

use JMGQ\AStar\AbstractNode;
use JMGQ\AStar\Node;

// Quick attempt from git@github.com:jmgq/php-a-star.git
class MyNode implements Node
{
    private $parent;
    private $children = array();

    private $gScore;
    private $hScore;

    private $row;
    private $column;

    public function __construct($row, $column)
    {
        $this->row = $row;
        $this->column = $column;
    }

    /**
     * @inheritdoc
     */
    public function setParent(Node $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @inheritdoc
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @inheritdoc
     */
    public function addChild(Node $child)
    {
        $child->setParent($this);

        $this->children[] = $child;
    }

    /**
     * @inheritdoc
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @inheritdoc
     * NOTE : avoid to inherit from AbstractNode tue to avoid perfomance hit on this one
     */
    public function getF()
    {
        return $this->gScore + $this->hScore;
    }

    /**
     * @inheritdoc
     */
    public function setG($score)
    {
        if (!is_numeric($score)) {
            throw new \InvalidArgumentException('The G value is not a number');
        }

        $this->gScore = $score;
    }

    /**
     * @inheritdoc
     */
    public function getG()
    {
        return $this->gScore;
    }

    /**
     * @inheritdoc
     */
    public function setH($score)
    {
        if (!is_numeric($score)) {
            throw new \InvalidArgumentException('The H value is not a number');
        }

        $this->hScore = $score;
    }

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function getID()
    {
        return $this->row . 'x' . $this->column;
    }
}
