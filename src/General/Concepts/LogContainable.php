<?php

namespace Magpie\General\Concepts;

use Magpie\Logs\Concepts\Loggable;

/**
 * May utilize a logging interface to contain logs
 */
interface LogContainable
{
    /**
     * Set the logger target to receive log messages from this item
     * @param Loggable $logger
     * @return bool If successfully applied
     */
    public function setLogger(Loggable $logger) : bool;
}