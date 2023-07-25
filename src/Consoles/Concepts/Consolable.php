<?php

namespace Magpie\Consoles\Concepts;

use Magpie\Codecs\Parsers\Parser;
use Magpie\Consoles\DisplayStyle;
use Magpie\Consoles\Inputs\PromptWithOption;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\TypeClassable;
use Stringable;

/**
 * May provide console output feature
 */
interface Consolable extends TypeClassable
{
    /**
     * Output a text to console with 'emergency' style
     * @param Stringable|string|null $text
     * @return void
     */
    public function emergency(Stringable|string|null $text) : void;


    /**
     * Output a text to console with 'alert' style
     * @param Stringable|string|null $text
     * @return void
     */
    public function alert(Stringable|string|null $text) : void;


    /**
     * Output a text to console with 'critical' style
     * @param Stringable|string|null $text
     * @return void
     */
    public function critical(Stringable|string|null $text) : void;


    /**
     * Output a text to console with 'error' style
     * @param Stringable|string|null $text
     * @return void
     */
    public function error(Stringable|string|null $text) : void;


    /**
     * Output a text to console with 'warning' style
     * @param Stringable|string|null $text
     * @return void
     */
    public function warning(Stringable|string|null $text) : void;


    /**
     * Output a text to console with 'notice' style
     * @param Stringable|string|null $text
     * @return void
     */
    public function notice(Stringable|string|null $text) : void;


    /**
     * Output a text to console with 'info' style
     * @param Stringable|string|null $text
     * @return void
     */
    public function info(Stringable|string|null $text) : void;


    /**
     * Output a text to console with 'debug' style
     * @param Stringable|string|null $text
     * @return void
     */
    public function debug(Stringable|string|null $text) : void;


    /**
     * Output a text to console with (optionally) a display style
     * @param Stringable|string|null $text
     * @param DisplayStyle|null $style
     * @return void
     */
    public function output(Stringable|string|null $text, ?DisplayStyle $style = null) : void;


    /**
     * Display on console
     * @param ConsoleDisplayable|null $target
     * @return void
     */
    public function display(?ConsoleDisplayable $target) : void;


    /**
     * A value is required (mandatory) from console input
     * @param PromptWithOption|Stringable|string|null $prompt
     * @param Parser<T>|null $parser
     * @return T
     * @template T
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public function requires(PromptWithOption|Stringable|string|null $prompt, ?Parser $parser = null) : mixed;


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
    public function optional(PromptWithOption|Stringable|string|null $prompt, ?Parser $parser = null, mixed $default = null) : mixed;


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
    public function requiresLoop(PromptWithOption|Stringable|string|null $prompt, ?int $maxTries = null, ?Parser $parser = null) : mixed;


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
    public function optionalLoop(PromptWithOption|Stringable|string|null $prompt, ?int $maxTries = null, ?Parser $parser = null, mixed $default = null) : mixed;
}