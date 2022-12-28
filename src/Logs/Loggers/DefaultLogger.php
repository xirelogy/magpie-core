<?php

namespace Magpie\Logs\Loggers;

use Magpie\Logs\Concepts\Loggable;
use Magpie\Logs\Concepts\LogRelayable;
use Magpie\Logs\LogEntry;
use Magpie\Logs\Logger;
use Magpie\Logs\LogLevel;
use Stringable;

/**
 * Default implementation of logger
 */
class DefaultLogger extends Logger
{
    /**
     * @var LogRelayable Next level relay
     */
    protected LogRelayable $relay;


    /**
     * Constructor
     * @param LogRelayable $relay
     */
    public function __construct(LogRelayable $relay)
    {
        $this->relay = $relay;
    }


    /**
     * @inheritDoc
     */
    public function log(mixed $level, Stringable|string $message, array $context = []) : void
    {
        $entry = new LogEntry(
            $this->relay->getSource(),
            static::acceptLogLevel($level, LogLevel::INFO),
            $message,
            $context,
        );

        $this->relay->log($entry);
    }


    /**
     * @inheritDoc
     */
    public function split(string $source) : Loggable
    {
        $relay = $this->relay->split($source);
        return new static($relay);
    }
}