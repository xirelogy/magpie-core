<?php

namespace Magpie\Logs\Formats;

use Carbon\Carbon;
use Magpie\Logs\LogConfig;
use Magpie\Logs\LogEntry;
use Magpie\Logs\LogLevel;

/**
 * Simple implementation of `LogStringFormattable`
 */
class SimpleLogStringFormat extends CommonLogStringFormat
{
    /**
     * @inheritDoc
     */
    public function format(LogEntry $entry, LogConfig $config) : string
    {
        $loggedAt = $entry->loggedAt ?? Carbon::now()->toImmutable();
        $loggedAt = $loggedAt->setTimezone($config->timezone)->format($config->timeFormat);

        $source = $entry->source ?? $config->defaultSource;
        $levelText = static::translateLevel($entry->level);

        $message = $entry->message;
        $ret = "[$loggedAt] $source #[$levelText] $message";

        if (count($entry->context) > 0) {
            $flattenContext = $this->flattenContext($entry->context);
            $ret .= " $flattenContext";
        }

        return static::normalize($ret);
    }


    /**
     * Translate the level text
     * @param LogLevel $level
     * @return string
     */
    protected static function translateLevel(LogLevel $level) : string
    {
        return $level->name;
    }
}