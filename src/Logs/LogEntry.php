<?php

namespace Magpie\Logs;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Log message entry
 */
class LogEntry
{
    /**
     * @var string|null Source (channel) providing the log
     */
    public ?string $source;
    /**
     * @var LogLevel Log level
     */
    public LogLevel $level;
    /**
     * @var string Message payload
     */
    public string $message;
    /**
     * @var array Associated context to the message
     */
    public array $context;
    /**
     * @var CarbonImmutable|null When the log message is created
     */
    public ?CarbonImmutable $loggedAt;


    /**
     * Constructor
     * @param string|null $source
     * @param LogLevel $level
     * @param string $message
     * @param array $context
     * @param CarbonInterface|null $loggedAt
     */
    public function __construct(?string $source, LogLevel $level, string $message, array $context, ?CarbonInterface $loggedAt = null)
    {
        $this->source = $source;
        $this->level = $level;
        $this->message = $message;
        $this->context = $context;
        $this->loggedAt = $loggedAt?->toImmutable() ?? null;
    }
}