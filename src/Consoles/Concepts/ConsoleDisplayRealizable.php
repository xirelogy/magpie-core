<?php

namespace Magpie\Consoles\Concepts;

/**
 * May realize console display
 */
interface ConsoleDisplayRealizable
{
    /**
     * Realize console display
     * @param ConsoleServiceable $service
     * @param ConsoleDisplayable $target
     * @return void
     */
    public static function realize(ConsoleServiceable $service, ConsoleDisplayable $target) : void;
}