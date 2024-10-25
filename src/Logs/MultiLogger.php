<?php

namespace Magpie\Logs;

use Magpie\Logs\Concepts\Loggable;
use Stringable;

/**
 * Multiple logger implementation (1-to-many)
 */
class MultiLogger extends Logger
{
    /**
     * @var array<Loggable> Downstream loggers
     */
    protected readonly array $loggers;


    /**
     * Constructor
     * @param iterable<Loggable> $loggers Downstream loggers
     */
    public function __construct(iterable $loggers)
    {
        $this->loggers = iter_flatten($loggers, false);
    }


    /**
     * @inheritDoc
     */
    public function log($level, Stringable|string $message, array $context = []) : void
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }


    /**
     * @inheritDoc
     */
    public function split(string $source) : Loggable
    {
        $splitLoggers = [];
        foreach ($this->loggers as $logger) {
            $splitLoggers[] = $logger->split($source);
        }

        return new static($splitLoggers);
    }
}