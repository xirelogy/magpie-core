<?php

namespace Magpie\Logs\Concepts;

use Magpie\Logs\LogConfig;
use Magpie\Logs\LogEntry;

interface LogStringFormattable
{
    /**
     * Apply formatting to log entry
     * @param LogEntry $entry
     * @param LogConfig $config
     * @return string
     */
    public function format(LogEntry $entry, LogConfig $config) : string;
}