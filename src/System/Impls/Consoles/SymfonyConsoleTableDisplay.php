<?php

namespace Magpie\System\Impls\Consoles;

use Magpie\Consoles\Concepts\ConsoleDisplayable;
use Magpie\Consoles\ConsoleTable;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Symfony\Component\Console\Helper\Table as SymfonyTable;
use Symfony\Component\Console\Output\ConsoleOutputInterface as SymfonyConsoleOutputInterface;

/**
 * Adapter to handle ConsoleTable to SymfonyConsole
 * @internal
 */
#[FactoryTypeClass(SymfonyConsoleTableDisplay::TYPECLASS, SymfonyConsoleDisplay::class)]
class SymfonyConsoleTableDisplay extends SymfonyConsoleDisplay
{
    /**
     * Current type class
     */
    public const TYPECLASS = ConsoleTable::TYPECLASS;


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function onDisplay(ConsoleDisplayable $target, SymfonyConsoleOutputInterface $outputBackend) : void
    {
        if (!$target instanceof ConsoleTable) return;

        $exported = $target->_export();

        $outTable = new SymfonyTable($outputBackend);
        $outTable->setHeaders($exported->headers);
        $outTable->setRows($exported->rows);
        $outTable->setStyle('default');
        $outTable->render();
    }
}