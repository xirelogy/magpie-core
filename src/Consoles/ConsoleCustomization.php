<?php

namespace Magpie\Consoles;

/**
 * May customize the console
 */
abstract class ConsoleCustomization
{
    /**
     * @var array<string, string> Manual styles
     */
    protected array $manualStyles = [];


    /**
     * Define custom console styles (and their colors)
     * @return iterable<string, string>
     */
    public final function getConsoleStyles() : iterable
    {
        yield from $this->onGetConsoleStyles();
        yield from $this->manualStyles;
    }


    /**
     * Define a manual custom console style (and its color)
     * @param string $name
     * @param string $color
     * @return void
     */
    public final function defineManualConsoleStyle(string $name, string $color) : void
    {
        $this->manualStyles[$name] = $color;
    }


    /**
     * Define specifically custom console styles (and their colors)
     * @return iterable<string, string>
     */
    protected function onGetConsoleStyles() : iterable
    {
        return [];
    }


    /**
     * A default instance
     * @return static
     */
    public static function default() : self
    {
        return new class extends ConsoleCustomization {

        };
    }
}
