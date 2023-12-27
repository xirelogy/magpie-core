<?php

namespace Magpie\Logs\Formats;

use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Sugars\Excepts;
use Magpie\Logs\Concepts\LogStringFormattable;

/**
 * Common implementation support for `LogStringFormattable`
 */
abstract class CommonLogStringFormat implements LogStringFormattable
{
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
}