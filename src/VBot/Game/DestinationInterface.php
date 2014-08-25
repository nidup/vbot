<?php

namespace VBot\Game;

/**
 * Destination interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface DestinationInterface
{
    /**
     * @return integer
     */
    public function getPosX();

    /**
     * @return integer
     */
    public function getPosY();
}
