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
     * @var string|null Specific working directory to be used (if not inherited)
     */
    protected ?string $workDir = null;
    /**
     * @var array<string, string>|null Specific environment variables to be used (if not inherited)
     */
    protected ?array $env = null;


    /**
     * Constructor
     * @param string ...$arguments
     */
    public function __construct(string ...$arguments)
    {
        $this->arguments = $arguments;
    }


    /**
     * Specify the specific working directory
     * @param string $workDir
     * @return $this
     */
    public function withWorkDir(string $workDir) : static
    {
        $this->workDir = $workDir;
        return $this;
    }


    /**
     * Access to the specific working directory
     * @return string|null
     */
    public function getWorkDir() : ?string
    {
        return $this->workDir;
    }


    /**
     * Specify the specific environment variables
     * @param iterable<string, string> $vars
     * @return $this
     */
    public function withEnvironment(iterable $vars) : static
    {
        $this->env = iter_flatten($vars);
        return $this;
    }


    /**
     * Access to the specific environment variables
     * @return string[]|null
     */
    public function getEnvironment() : ?array
    {
        return $this->env;
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