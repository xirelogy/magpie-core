<?php

namespace Magpie\Tasks\Context;

use Carbon\Carbon;
use Magpie\Facades\Random;
use Magpie\General\DateTimes\SystemTimezone;
use Magpie\General\Randoms\RandomCharset;
use Magpie\Logs\Concepts\Loggable;
use Magpie\Logs\LogConfig;
use Magpie\Logs\Loggers\DefaultLogger;
use Magpie\Logs\Relays\SpecificFileLogRelay;
use Magpie\System\Kernel\Kernel;
use Magpie\Tasks\Task;

/**
 * May setup task context on redirecting all logging to specific file relay with specific naming rules
 */
class NamedFileTaskContextLoggingSetup extends TaskContextLoggingSetup
{
    /**
     * @var string|null Log source to be used
     */
    protected readonly ?string $logSource;
    /**
     * @var LogConfig Log configuration to be used
     */
    protected readonly LogConfig $logConfig;


    /**
     * Constructor
     * @param string|null $logSource
     * @param LogConfig|null $logConfig
     */
    protected function __construct(?string $logSource, ?LogConfig $logConfig)
    {
        $this->logSource = $logSource;
        $this->logConfig = $logConfig ?? Kernel::current()->getConfig()->createDefaultLogConfig();
    }


    /**
     * @inheritDoc
     */
    protected final function createLogger(Task $parentTask, TaskContext $parentContext) : Loggable
    {
        $dir = $this->getLoggerRelDirectory($parentTask, $parentContext);

        $name = $this->createLoggerName($parentTask, $parentContext);
        $name = str_replace('/', '-', $name);
        $name = str_replace('\\', '-', $name);

        $relay = new SpecificFileLogRelay("$dir/$name", $this->logConfig, $this->logSource);
        return new DefaultLogger($relay);
    }


    /**
     * Specify the relative directory for the logger file
     * @param Task $parentTask
     * @param TaskContext $parentContext
     * @return string
     */
    protected function getLoggerRelDirectory(Task $parentTask, TaskContext $parentContext) : string
    {
        _used($parentTask, $parentContext);

        return 'tasks';
    }


    /**
     * Create a name for logger file
     * @param Task $parentTask
     * @param TaskContext $parentContext
     * @return string
     */
    protected function createLoggerName(Task $parentTask, TaskContext $parentContext) : string
    {
        _used($parentContext);

        $timezone = SystemTimezone::default();
        $timeFormat = 'Ymd-His';

        $now = Carbon::now($timezone);
        return $now->format($timeFormat) . ' ' . $parentTask->getName() . ' ' . static::createLoggerNameNonce() . '.log';
    }


    /**
     * Create a nonce string for logger file
     * @return string
     */
    protected static function createLoggerNameNonce() : string
    {
        return Random::string(6, RandomCharset::LOWER_ALPHANUM);
    }


    /**
     * Create an instance
     * @param string|null $logSource
     * @param LogConfig|null $logConfig
     * @return static
     */
    public static function create(?string $logSource = null, ?LogConfig $logConfig = null) : static
    {
        return new static($logSource, $logConfig);
    }
}