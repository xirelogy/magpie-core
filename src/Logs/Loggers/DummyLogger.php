<?php

namespace Magpie\Logs\Loggers;

use Magpie\Logs\Concepts\Loggable;
use Magpie\Logs\Logger;
use Stringable;

/**
 * A dummy logger
 */
class DummyLogger extends Logger
{
    /**
     * @inheritDoc
     */
    public function log(mixed $level, Stringable|string $message, array $context = []) : void
    {
        // Purposely NOP
    }


    /**
     * @inheritDoc
     */
    public function split(string $source) : Loggable
    {
        // Purposely NOP
        return new static();
    }
}