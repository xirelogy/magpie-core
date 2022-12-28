<?php

namespace Magpie\System\Traits;

use BadMethodCallException;

/**
 * Declare current class as non serializable
 */
trait NonSerializable
{
    /**
     * Magic method: __sleep
     * @return array
     */
    public function __sleep() : array
    {
        throw new BadMethodCallException('Cannot serialize ' . static::class);
    }


    /**
     * Magic method: __wakeup
     * @return void
     */
    public function __wakeup() : void
    {
        throw new BadMethodCallException('Cannot unserialize ' . static::class);
    }
}