<?php

namespace Magpie\Logs\Formats;

use Magpie\Logs\LogConfig;
use Magpie\Logs\LogEntry;

/**
 * Implementation of `LogStringFormattable` for console output with clean message only output
 */
class CleanConsoleLogStringFormat extends CommonLogStringFormat
{
    /**
     * @inheritDoc
     */
    public function format(LogEntry $entry, LogConfig $config) : string
    {
        return $entry->message;
    }
}