<?php

namespace Magpie\Facades;

use Magpie\Codecs\Parsers\Parser;
use Magpie\Consoles\Concepts\Consolable;
use Magpie\Consoles\Concepts\ConsoleDisplayable;
use Magpie\Consoles\ConsoleProvider;
use Magpie\Consoles\DisplayStyle;
use Magpie\Consoles\Inputs\PromptWithOption;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\General\Traits\StaticClass;
use Stringable;

/**
 * Console facade
 */
class Console
{
    use StaticClass;


    /**
     * Output a text to console with 'emergency' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function emergency(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::EMERGENCY);
    }


    /**
     * Output a text to console with 'alert' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function alert(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::ALERT);
    }


    /**
     * Output a text to console with 'critical' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function critical(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::CRITICAL);
    }


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
     * Output a text to console with 'notice' style
     * @param Stringable|string|null $text
     * @return void
     */
    public static function notice(Stringable|string|null $text) : void
    {
        static::output($text, DisplayStyle::NOTICE);
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


    /**
     * A value is required (mandatory) from console input
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param Parser<T>|null $parser
     * @return T
     * @template T
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function requires(PromptWithOption|Stringable|string|null $prompt, ?Parser $parser = null) : mixed
    {
        return static::ensureProvider()->requires($prompt, $parser);
    }


    /**
     * A value is optionally required from console input
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param Parser<T>|null $parser
     * @param T|null $default
     * @return T|null
     * @template T
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function optional(PromptWithOption|Stringable|string|null $prompt, ?Parser $parser = null, mixed $default = null) : mixed
    {
        return static::ensureProvider()->optional($prompt, $parser, $default);
    }


    /**
     * A value is required (mandatory) from console input.
     * Loop and retry until valid or maximum number of tries exceeded.
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param int|null $maxTries
     * @param Parser<T>|null $parser
     * @return T
     * @template T
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function requiresLoop(PromptWithOption|Stringable|string|null $prompt, ?int $maxTries = null, ?Parser $parser = null) : mixed
    {
        return static::ensureProvider()->requiresLoop($prompt, $maxTries, $parser);
    }


    /**
     * A value is optionally required from console input.
     * Loop and retry until valid or maximum number of tries exceeded.
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param int|null $maxTries
     * @param Parser|null $parser
     * @param T|null $default
     * @return T|null
     * @template T
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function optionalLoop(PromptWithOption|Stringable|string|null $prompt, ?int $maxTries = null, ?Parser $parser = null, mixed $default = null) : mixed
    {
        return static::ensureProvider()->optionalLoop($prompt, $maxTries, $parser, $default);
    }


    /**
     * Ensure that a provider is available
     * @return Consolable
     * @throws SafetyCommonException
     */
    protected static function ensureProvider() : Consolable
    {
        return ConsoleProvider::default() ?? throw new UnsupportedException(_l('No console provider available'));
    }
}