<?php

namespace Magpie\General\Sugars;

use Exception;
use Magpie\Exceptions\InvalidFormatArgumentException;
use Magpie\Exceptions\InvalidFormatStringException;
use Magpie\Exceptions\IndexOutOfRangeException;
use Magpie\Exceptions\StringFormatterException;
use Magpie\General\Str;
use Magpie\General\Traits\StaticClass;
use Stringable;

/**
 * Support for applying arguments to a format string, then providing the final
 * output string
 */
final class StringFormatter
{
    use StaticClass;


    /**
     * Apply arguments to given format string.
     * @param string $format Format string, where each placement is marked
     *                      around two brace brackets (`{{n}}`) with a zero-based
     *                      index as payload. When index is not specified then it
     *                      will take the current brace position.
     * @param mixed ...$args Arguments
     * @return string
     * @throws StringFormatterException
     * @noinspection PhpRedundantCatchClauseInspection
     */
    public static function format(string $format, mixed ...$args) : string
    {
        try {
            $ret = '';
            $totalArgs = count($args);

            $thisIndex = 0;
            $start = 0;
            while (true) {
                $startBracePos = strpos($format, '{{', $start);

                // When no start of brace detected, it is the end of the search
                if ($startBracePos === false) {
                    $ret .= substr($format, $start);
                    return $ret;
                }

                // Anything before the start of brace is copied intact
                $ret .= substr($format, $start, $startBracePos - $start);

                // The end brace is required
                $endBracePos = strpos($format, '}}', $startBracePos);
                if ($endBracePos === false) throw new Exception(_l('End brace is expected but not found'));

                // Try to determine the current index
                $index = static::decodeIndex(substr($format, $startBracePos + 2, $endBracePos - $startBracePos - 2), $thisIndex);

                // Check index and apply argument
                if ($index < 0 || $index >= $totalArgs) throw new IndexOutOfRangeException($index);
                $ret .= static::flatten($args[$index]);

                $start = $endBracePos + 2;
                ++$thisIndex;
            }
        } catch (StringFormatterException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new InvalidFormatStringException(previous: $ex);
        }
    }


    /**
     * Flatten argument into string
     * @param mixed $arg
     * @return string
     * @throws Exception
     */
    protected static function flatten(mixed $arg) : string
    {
        // Null is always converted to empty string
        if ($arg === null) return '';

        // String, numeric, and booleans
        if (is_string($arg)) return $arg;
        if (is_numeric($arg)) return $arg;
        if (is_bool($arg)) return StringOf::target($arg);

        // Scalar types can be handled directly over here, if not already handled above
        if (is_scalar($arg)) return "$arg";

        // Make use of __toString()
        if ($arg instanceof Stringable) return $arg->__toString();
        if (is_object($arg) && method_exists($arg, '__toString')) return $arg->__toString();

        // Arrays are supported
        if (is_array($arg)) return static::flattenArray($arg);

        // Give up!
        throw new InvalidFormatArgumentException($arg);
    }


    /**
     * Flatten array argument into string
     * @param array $values
     * @return string
     * @throws Exception
     */
    protected static function flattenArray(array $values) : string
    {
        $rets = [];
        foreach ($values as $value) {
            $rets[] = static::flatten($value);
        }

        return Quote::square(implode(', ', $rets));
    }


    /**
     * Decode the index
     * @param string $text
     * @param int $thisIndex
     * @return int
     * @throws Exception
     */
    protected static function decodeIndex(string $text, int $thisIndex) : int
    {
        if (strlen($text) <= 0) return $thisIndex;

        if (!Str::isInteger($text)) throw new Exception(_l('Index must be integer'));

        return intval($text);
    }
}