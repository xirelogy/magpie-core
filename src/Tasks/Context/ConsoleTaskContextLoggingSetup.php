<?php

namespace Magpie\Tasks\Context;

use Magpie\Facades\Console;
use Magpie\Logs\Concepts\Loggable;
use Magpie\Logs\Concepts\LogStringFormattable;
use Magpie\Logs\LogConfig;
use Magpie\Tasks\Task;

/**
 * May setup task context on redirecting all logging to console
 */
class ConsoleTaskContextLoggingSetup extends TaskContextLoggingSetup
{
    /**
     * @var LogStringFormattable|null Specific formatter
     */
    protected readonly ?LogStringFormattable $logFormatter;
    /**
     * @var LogConfig|null Specific config
     */
    protected readonly ?LogConfig $logConfig;


    /**
     * Constructor
     * @param LogStringFormattable|null $logFormatter
     * @param LogConfig|null $logConfig
     */
    protected function __construct(?LogStringFormattable $logFormatter, ?LogConfig $logConfig)
    {
        $this->logFormatter = $logFormatter;
        $this->logConfig = $logConfig;
    }


    /**
     * @inheritDoc
     */
    protected function createLogger(Task $parentTask, TaskContext $parentContext) : Loggable
    {
        return Console::asLogger();
    }


    /**
     * Create an instance
     * @param LogStringFormattable|null $logFormatter
     * @param LogConfig|null $logConfig
     * @return static
     */
    public static function create(?LogStringFormattable $logFormatter = null, ?LogConfig $logConfig = null) : static
    {
        return new static($logFormatter, $logConfig);
    }
}