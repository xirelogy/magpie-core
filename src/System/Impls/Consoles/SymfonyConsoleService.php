<?php

namespace Magpie\System\Impls\Consoles;

use Magpie\Consoles\Concepts\ConsoleServiceable;
use Symfony\Component\Console\Output\ConsoleOutputInterface as SymfonyConsoleOutputInterface;

/**
 * Console service interface for SymfonyConsole
 * @internal
 */
class SymfonyConsoleService implements ConsoleServiceable
{
    /**
     * @var SymfonyConsoleOutputInterface Output backend
     */
    public readonly SymfonyConsoleOutputInterface $outputBackend;


    /**
     * Constructor
     * @param SymfonyConsoleOutputInterface $outputBackend
     */
    public function __construct(SymfonyConsoleOutputInterface $outputBackend)
    {
        $this->outputBackend = $outputBackend;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return SymfonyConsole::TYPECLASS;
    }
}