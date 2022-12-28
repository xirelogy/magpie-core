<?php

namespace Magpie\Logs\Formats;

use Carbon\Carbon;
use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Sugars\Excepts;
use Magpie\Logs\Concepts\LogStringFormattable;
use Magpie\Logs\LogConfig;
use Magpie\Logs\LogEntry;
use Magpie\Logs\LogLevel;

/**
 * Simple implementation of `LogStringFormattable`
 */
class SimpleLogStringFormat implements LogStringFormattable
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
     * Normalize output
     * @param string $text
     * @return string
     */
    protected static function normalize(string $text) : string
    {
        // Trim at right end
        $text = rtrim($text);

        // All `\r\n` must be converted to plain `\n`
        $text = str_replace("\r\n", "\n", $text);

        // Plain `\r` leftover are converted to `\n`
        $text = str_replace("\r", "\n", $text);

        // Delete all trailing `\n`
        while (str_ends_with("\n", $text)) {
            $text = substr($text, 0, -1);
        }

        // Extend output into proper indentations
        return str_replace("\n", "\n  ", $text);
    }


    /**
     * Flatten context string
     * @param array $context
     * @return string
     */
    protected function flattenContext(array $context) : string
    {
        return Excepts::noThrow(fn () => SimpleJSON::encode($context), '<err>');
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