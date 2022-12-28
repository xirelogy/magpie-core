<?php

namespace Magpie\System\Process;

use Magpie\Exceptions\UnsupportedException;
use Magpie\System\Impls\ProcessSupport;

/**
 * A command line to start a new process
 */
class ProcessCommandLine
{
    /**
     * @var array<string> Arguments
     */
    public readonly array $arguments;


    /**
     * Constructor
     * @param string ...$arguments
     */
    public function __construct(string ...$arguments)
    {
        $this->arguments = $arguments;
    }


    /**
     * Create a new command line to run a specific PHP script
     * @param string $scriptName
     * @param string ...$scriptArguments
     * @return static
     * @throws UnsupportedException
     */
    public static function fromPhp(string $scriptName, string ...$scriptArguments) : static
    {
        $phpPath = ProcessSupport::getPhpPath() ?? throw new UnsupportedException();

        return new static($phpPath, $scriptName, ...$scriptArguments);
    }


    /**
     * Create a new command line to run a specific console command
     * @param string $command
     * @param string ...$commandArguments
     * @return static
     * @throws UnsupportedException
     */
    public static function fromCommand(string $command, string ...$commandArguments) : static
    {
        $consoleScript = ProcessSupport::getConsoleCommandScriptPath();

        return static::fromPhp($consoleScript, $command, ...$commandArguments);
    }
}