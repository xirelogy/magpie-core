<?php

namespace Magpie\Logs\Loggers;

use Magpie\General\Contexts\ClosureReleaseScoped;
use Magpie\General\Contexts\Scoped;
use Magpie\Logs\Concepts\Loggable;
use Stringable;

/**
 * A logger that can switch forwarded logger
 */
class ForwardingLogger implements Loggable
{
    /**
     * @var Loggable Next logger to be forwarded to
     */
    protected Loggable $nextLogger;


    /**
     * Constructor
     * @param Loggable $nextLogger Next logger to forward logs to
     */
    public function __construct(Loggable $nextLogger)
    {
        $this->nextLogger = $nextLogger;
    }


    /**
     * @inheritDoc
     */
    public function emergency(Stringable|string $message, array $context = []) : void
    {
        $this->nextLogger->emergency($message, $context);
    }


    /**
     * @inheritDoc
     */
    public function alert(Stringable|string $message, array $context = []) : void
    {
        $this->nextLogger->alert($message, $context);
    }


    /**
     * @inheritDoc
     */
    public function critical(Stringable|string $message, array $context = []) : void
    {
        $this->nextLogger->critical($message, $context);
    }


    /**
     * @inheritDoc
     */
    public function error(Stringable|string $message, array $context = []) : void
    {
        $this->nextLogger->error($message, $context);
    }


    /**
     * @inheritDoc
     */
    public function warning(Stringable|string $message, array $context = []) : void
    {
        $this->nextLogger->warning($message, $context);
    }


    /**
     * @inheritDoc
     */
    public function notice(Stringable|string $message, array $context = []) : void
    {
        $this->nextLogger->notice($message, $context);
    }


    /**
     * @inheritDoc
     */
    public function info(Stringable|string $message, array $context = []) : void
    {
        $this->nextLogger->info($message, $context);
    }


    /**
     * @inheritDoc
     */
    public function debug(Stringable|string $message, array $context = []) : void
    {
        $this->nextLogger->debug($message, $context);
    }


    /**
     * @inheritDoc
     */
    public function log(mixed $level, Stringable|string $message, array $context = []) : void
    {
        $this->nextLogger->log($level, $message, $context);
    }


    /**
     * @inheritDoc
     */
    public function split(string $source) : Loggable
    {
        return $this->nextLogger->split($source);
    }


    /**
     * Lock the current next level logger for further usage
     * @return Loggable
     */
    public function lock() : Loggable
    {
        return $this->nextLogger;
    }


    /**
     * Switch and change the next logger
     * @param Loggable $nextLogger
     * @return Scoped A scope that upon completion, revert to the last logger
     */
    public function switch(Loggable $nextLogger) : Scoped
    {
        $oldNextLogger = $this->nextLogger;
        $this->nextLogger = $nextLogger;

        return ClosureReleaseScoped::create(function () use ($oldNextLogger) {
            $this->nextLogger = $oldNextLogger;
        });
    }
}