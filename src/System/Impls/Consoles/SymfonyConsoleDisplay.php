<?php

namespace Magpie\System\Impls\Consoles;

use Magpie\Consoles\Concepts\ConsoleDisplayable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Traits\StaticClass;
use Symfony\Component\Console\Output\ConsoleOutputInterface as SymfonyConsoleOutputInterface;

/**
 * Adapter to handle ConsoleDisplayable to SymfonyConsole
 * @internal
 */
abstract class SymfonyConsoleDisplay implements TypeClassable
{
    use StaticClass;


    /**
     * Shows display target to the given output backend
     * @param ConsoleDisplayable $target
     * @param SymfonyConsoleOutputInterface $outputBackend
     * @return void
     */
    public static final function display(ConsoleDisplayable $target, SymfonyConsoleOutputInterface $outputBackend) : void
    {
        $className = ClassFactory::safeResolve($target::getTypeClass(), self::class);
        if ($className === null) return;
        if (!is_subclass_of($className, self::class)) return;

        $className::onDisplay($target, $outputBackend);
    }


    /**
     * Actually shows display target to the given output backend
     * @param ConsoleDisplayable $target
     * @param SymfonyConsoleOutputInterface $outputBackend
     * @return void
     */
    protected static abstract function onDisplay(ConsoleDisplayable $target, SymfonyConsoleOutputInterface $outputBackend) : void;
}