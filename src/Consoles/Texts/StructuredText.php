<?php

namespace Magpie\Consoles\Texts;

use Magpie\Consoles\DisplayStyle;
use Stringable;

/**
 * Structured text
 */
abstract class StructuredText implements Stringable
{
    /**
     * Create compound texts
     * @param self|string ...$texts
     * @return self
     */
    public static function compound(self|string ...$texts) : self
    {
        return new CompoundStructuredText($texts);
    }


    /**
     * Create single unit text
     * @param string $text
     * @param DisplayStyle|string|null $format
     * @return self
     */
    public static function from(string $text, DisplayStyle|string|null $format = null) : self
    {
        return new UnitStructuredText($text, $format);
    }


    /**
     * Create single unit text with emergency format
     * @param string $text
     * @return self
     */
    public static function emergency(string $text) : self
    {
        return static::from($text, DisplayStyle::EMERGENCY);
    }


    /**
     * Create single unit text with alert format
     * @param string $text
     * @return self
     */
    public static function alert(string $text) : self
    {
        return static::from($text, DisplayStyle::ALERT);
    }


    /**
     * Create single unit text with critical format
     * @param string $text
     * @return self
     */
    public static function critical(string $text) : self
    {
        return static::from($text, DisplayStyle::CRITICAL);
    }


    /**
     * Create single unit text with error format
     * @param string $text
     * @return self
     */
    public static function error(string $text) : self
    {
        return static::from($text, DisplayStyle::ERROR);
    }


    /**
     * Create single unit text with warning format
     * @param string $text
     * @return self
     */
    public static function warning(string $text) : self
    {
        return static::from($text, DisplayStyle::WARNING);
    }


    /**
     * Create single unit text with notice format
     * @param string $text
     * @return self
     */
    public static function notice(string $text) : self
    {
        return static::from($text, DisplayStyle::NOTICE);
    }


    /**
     * Create single unit text with info format
     * @param string $text
     * @return self
     */
    public static function info(string $text) : self
    {
        return static::from($text, DisplayStyle::INFO);
    }


    /**
     * Create single unit text with note format
     * @param string $text
     * @return self
     * @deprecated use debug() instead
     */
    public static function note(string $text) : self
    {
        return static::from($text, DisplayStyle::NOTE);
    }


    /**
     * Create single unit text with strong format
     * @param string $text
     * @return self
     * @deprecated use notice() instead
     */
    public static function strong(string $text) : self
    {
        return static::from($text, DisplayStyle::STRONG);
    }


    /**
     * Create single unit text with debug format
     * @param string $text
     * @return self
     */
    public static function debug(string $text) : self
    {
        return static::from($text, DisplayStyle::DEBUG);
    }
}