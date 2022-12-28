<?php

namespace Magpie\Consoles\Concepts;

use Magpie\Consoles\DisplayStyle;
use Magpie\General\Concepts\TypeClassable;
use Stringable;

/**
 * May provide console output feature
 */
interface Consolable extends TypeClassable
{
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
}