<?php

namespace Magpie\Facades;

use Magpie\Consoles\Concepts\ConsoleDisplayable;
use Magpie\Consoles\ConsoleProvider;
use Magpie\Consoles\DisplayStyle;
use Magpie\General\Traits\StaticClass;
use Stringable;

/**
 * Console facade
 */
class Console
{
    use StaticClass;


    /**
     * Output a text to console with 'error' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function error(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::ERROR);
    }


    /**
     * Output a text to console with 'warning' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function warning(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::WARNING);
    }


    /**
     * Output a text to console with 'info' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function info(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::INFO);
    }


    /**
     * Output a text to console with 'debug' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function debug(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::DEBUG);
    }


    /**
     * Output a text to console with (optionally) a display style
     * @param Stringable|string|null $text
     * @param DisplayStyle|null $style
     * @return void
     */
    public static function output(Stringable|string|null $text, ?DisplayStyle $style = null) : void
    {
        ConsoleProvider::default()?->output($text, $style);
    }


    /**
     * Display on console
     * @param ConsoleDisplayable|null $target
     * @return void
     */
    public static function display(?ConsoleDisplayable $target) : void
    {
        ConsoleProvider::default()?->display($target);
    }
}