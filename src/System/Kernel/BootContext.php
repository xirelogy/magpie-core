<?php

namespace Magpie\System\Kernel;

/**
 * Boot context
 */
abstract class BootContext
{
    /**
     * If given class had booted up
     * @param string $className
     * @return bool
     */
    public abstract function isBooted(string $className) : bool;
}