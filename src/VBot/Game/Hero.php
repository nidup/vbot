<?php

namespace VBot\Game;

/**
 * Hero model
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class Hero extends AbstractPlayer
{
    protected $target = null;

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
     * @param DestinationInterface|null $target
     */
    public function resetTarget()
    {
        echo 'RESET TARGET'.PHP_EOL;
        $this->target = null;
    }
}
