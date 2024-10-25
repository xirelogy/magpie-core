<?php

namespace Magpie\Logs\Formats;

use Magpie\General\Str;
use Magpie\Logs\LogConfig;
use Magpie\Logs\LogEntry;

/**
 * Simple implementation of `LogStringFormattable` for console output
 */
class SimpleConsoleLogStringFormat extends CommonLogStringFormat
{
    /**
     * @inheritDoc
     */
    public function format(LogEntry $entry, LogConfig $config) : string
    {
        $source = $entry->source ?? $config->defaultSource;

        $message = $entry->message;

        if (Str::isNullOrEmpty($source)) {
            $ret = $message;
        } else {
            $ret = "[$source] $message";
        }

        if (count($entry->context) > 0) {
            $flattenContext = $this->flattenContext($entry->context);
            $ret .= " $flattenContext";
        }

        return static::normalize($ret);
    }
}