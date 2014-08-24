<?php

namespace VBot\Bot;

/**
 * Bot interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface BotInterface
{
    /**
     * TODO : could be replaced by a state object
     * @param array
     */
    public function move($state);
}
