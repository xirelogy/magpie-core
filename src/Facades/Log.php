<?php

namespace Magpie\Facades;

use Magpie\General\Traits\StaticClass;
use Magpie\Logs\Concepts\Loggable;
use Magpie\Logs\LogLevel;
use Magpie\System\Kernel\Kernel;
use Stringable;

/**
 * Log facade
 */
class Log
{
    use StaticClass;


    /**
     * Static facade to `emergency` log
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function emergency(Stringable|string $message, array $context = []) : void
    {
        static::log(LogLevel::EMERGENCY, $message, $context);
    }


    /**
     * Static facade to `alert` log
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function alert(Stringable|string $message, array $context = []) : void
    {
        static::log(LogLevel::ALERT, $message, $context);
    }


    /**
     * Static facade to `critical` log
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function critical(Stringable|string $message, array $context = []) : void
    {
        static::log(LogLevel::CRITICAL, $message, $context);
    }


    /**
     * Static facade to `error` log
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function error(Stringable|string $message, array $context = []) : void
    {
        static::log(LogLevel::ERROR, $message, $context);
    }


    /**
     * Static facade to `warning` log
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function warning(Stringable|string $message, array $context = []) : void
    {
        static::log(LogLevel::WARNING, $message, $context);
    }


    /**
     * Static facade to `notice` log
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function notice(Stringable|string $message, array $context = []) : void
    {
        static::log(LogLevel::NOTICE, $message, $context);
    }


    /**
     * Static facade to `info` log
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function info(Stringable|string $message, array $context = []) : void
    {
        static::log(LogLevel::INFO, $message, $context);
    }


    /**
     * Static facade to `debug` log
     * @param Stringable|string $message
     * @param array $context
     * @return void
     */
    public static function debug(Stringable|string $message, array $context = []) : void
    {
        static::log(LogLevel::DEBUG, $message, $context);
    }


    /**
     * Static facade to `log`
     * @param LogLevel $level
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public static function log(LogLevel $level, Stringable|string $message, array $context = []) : void
    {
        static::getLogger()->log($level, $message, $context);
    }


    /**
     * Split out an individual interface to log under specific sub-source
     * @param string $source
     * @return Loggable
     */
    public static function split(string $source) : Loggable
    {
        return static::getLogger()->split($source);
    }


    /**
     * Get instance of current logger
     * @return Loggable
     */
    public static function current() : Loggable
    {
        return static::getLogger();
    }


    /**
     * Get the default logger target
     * @return Loggable
     */
    protected static function getLogger() : Loggable
    {
        return Kernel::current()->getLogger();
    }
}