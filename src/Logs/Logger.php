<?php

namespace Magpie\Logs;

use Magpie\Logs\Concepts\Loggable;
use Stringable;

/**
 * Common logger implementation
 */
abstract class Logger implements Loggable
{
    /**
     * @inheritDoc
     */
    public function emergency(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }


    /**
     * @inheritDoc
     */
    public function alert(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }


    /**
     * @inheritDoc
     */
    public function critical(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }


    /**
     * @inheritDoc
     */
    public function error(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }


    /**
     * @inheritDoc
     */
    public function warning(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }


    /**
     * @inheritDoc
     */
    public function notice(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }


    /**
     * @inheritDoc
     */
    public function info(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }


    /**
     * @inheritDoc
     */
    public function debug(Stringable|string $message, array $context = []) : void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }


    /**
     * Accept and translate log level
     * @param mixed $level
     * @param LogLevel $defaultLevel
     * @return LogLevel
     */
    protected static function acceptLogLevel(mixed $level, LogLevel $defaultLevel) : LogLevel
    {
        if ($level instanceof LogLevel) return $level;
        if (is_int($level)) return LogLevel::tryFrom($level) ?? $defaultLevel;

        // Reject others
        return $defaultLevel;
    }
}