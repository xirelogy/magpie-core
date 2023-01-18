<?php

namespace Magpie\System\Impls;

use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\General\Traits\StaticClass;
use Magpie\System\Impls\Concepts\ProcessSupportable;
use Magpie\System\Kernel\Kernel;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessCommandLine;

/**
 * Functionalities to support process related features
 * @internal
 */
final class ProcessSupport
{
    use StaticClass;


    /**
     * Create a new process using given command line
     * @param ProcessCommandLine $commandLine
     * @return Process
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    public static function createProcess(ProcessCommandLine $commandLine) : Process
    {
        return static::getProvider()->createProcess($commandLine);
    }


    /**
     * Find the actual path of the PHP executable
     * @return string|null
     */
    public static function getPhpPath() : ?string
    {
        return static::getProvider()->getPhpPath();
    }


    /**
     * Get the actual path of the console command script
     * @return string
     */
    public static function getConsoleCommandScriptPath() : string
    {
        $command = 'mp';
        return project_path("/$command");
    }


    /**
     * Get provider
     * @return ProcessSupportable
     */
    protected static function getProvider() : ProcessSupportable
    {
        $instance = Kernel::current()->getProvider(ProcessSupportable::class);
        if ($instance instanceof ProcessSupportable) return $instance;

        return new SymfonyProcessSupport();
    }
}