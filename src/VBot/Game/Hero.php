<?php

namespace VBot\Game;

/**
 * Hero model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Hero extends AbstractPlayer
{
    /** @var DestinationInterface */
    protected $target = null;

    /** @var string */
    protected $direction = 'Stay';

    /**
     * @return DestinationInterface $target
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param DestinationInterface $target
     */
    public function setTarget(DestinationInterface $target)
    {
        echo 'CHANGE TARGET '.get_class($target).' x:y'.$target->getPosX().':'.$target->getPosY().PHP_EOL;
        $this->target = $target;
    }

    /**
     * Unset the target
     */
    public function resetTarget()
    {
        echo 'RESET TARGET'.PHP_EOL;
        $this->target = null;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * @param string
     */
    public function setDirection($direction)
    {
        $this->direction = $direction;
    }
}
