<?php

namespace Magpie\Commands\Impls;

use Magpie\Commands\Command;
use Magpie\Facades\Console;
use Magpie\General\Traits\StaticClass;
use Throwable;

/**
 * Handle exceptions in console's context
 * @internal
 */
class ConsoleExceptionHandler
{
    use StaticClass;


    /**
     * Exception handler specifically for console
     * @param Throwable $ex
     * @return int
     */
    public static function handle(Throwable $ex) : int
    {
        Console::error($ex->getMessage());
        Console::warning($ex->getTraceAsString());

        return Command::EXIT_FAILURE;
    }
}