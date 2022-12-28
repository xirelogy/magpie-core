<?php

namespace Magpie\System\Impls;

use Magpie\System\Impls\Concepts\ProcessSupportable;
use Magpie\System\Kernel\Kernel;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessCommandLine;
use Symfony\Component\Process\PhpExecutableFinder as SymfonyPhpExecutableFinder;

/**
 * Process functionalities implementation provider using Symfony backend
 * @internal
 */
class SymfonyProcessSupport implements ProcessSupportable
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'symfony';


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
    public function registerAsDefaultProvider() : void
    {
        Kernel::current()->registerProvider(ProcessSupportable::class, $this);
    }


    /**
     * @inheritDoc
     */
    public function createProcess(ProcessCommandLine $commandLine) : Process
    {
        return new SymfonyProcess($commandLine);
    }


    /**
     * @inheritDoc
     */
    public function getPhpPath() : ?string
    {
        $ret = (new SymfonyPhpExecutableFinder())->find(false);
        if ($ret === false) return null;

        return $ret;
    }
}