<?php

namespace Magpie\System\Impls\Consoles\Realizers;

use Magpie\Consoles\Concepts\ConsoleDisplayable;
use Magpie\Consoles\Concepts\ConsoleDisplayRealizable;
use Magpie\Consoles\Concepts\ConsoleServiceable;
use Magpie\Consoles\ConsoleTable;
use Magpie\General\Factories\Annotations\FeatureMatrixTypeClass;
use Magpie\System\Impls\Consoles\SymfonyConsole;
use Magpie\System\Impls\Consoles\SymfonyConsoleService;
use Symfony\Component\Console\Helper\Table as SymfonyTable;

/**
 * Display table on SymfonyConsole
 */
#[FeatureMatrixTypeClass(ConsoleTable::TYPECLASS, SymfonyConsole::TYPECLASS, ConsoleDisplayRealizable::class)]
class SymfonyConsoleTableDisplayRealizer implements ConsoleDisplayRealizable
{
    /**
     * @inheritDoc
     */
    public static function realize(ConsoleServiceable $service, ConsoleDisplayable $target) : void
    {
        if (!$service instanceof SymfonyConsoleService) return;
        if (!$target instanceof ConsoleTable) return;

        $exported = $target->_export();

        $outTable = new SymfonyTable($service->outputBackend);
        $outTable->setHeaders($exported->headers);
        $outTable->setRows($exported->rows);
        $outTable->setStyle('default');
        $outTable->render();
    }
}