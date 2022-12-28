<?php

namespace Magpie\Models;

use Magpie\Facades\Log;
use Magpie\Logs\Concepts\Loggable;
use Magpie\Logs\LogLevel;
use Magpie\Models\Concepts\StatementLogListenable;

/**
 * Listen to log query statements and output to a logger
 */
class LoggerStatementLogListener implements StatementLogListenable
{
    /**
     * @var Loggable Attached logger
     */
    protected Loggable $logger;
    /**
     * @var LogLevel Corresponding log level
     */
    protected LogLevel $level;
    /**
     * @var string Prefix when logged
     */
    protected string $prefix;


    /**
     * Constructor
     * @param Loggable|null $logger
     * @param LogLevel $level
     * @param string $prefix
     */
    protected function __construct(?Loggable $logger, LogLevel $level, string $prefix)
    {
        $this->logger = $logger ?? Log::current();
        $this->level = $level;
        $this->prefix = $prefix;
    }


    /**
     * @inheritDoc
     */
    public function logStatement(RawStatement $statement) : void
    {
        $content = $this->prefix . $statement->sql;

        $this->logger->log($this->level, $content, $statement->values);
    }


    /**
     * Create an instance
     * @param Loggable|null $logger Receiving logger, or the default logger if not provided
     * @param LogLevel $level The log level to be used
     * @param string $prefix Prefix to the log, if provided
     * @return static
     */
    public static function create(?Loggable $logger = null, LogLevel $level = LogLevel::INFO, string $prefix = '') : static
    {
        return new static($logger, $level, $prefix);
    }
}