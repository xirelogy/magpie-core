<?php

namespace Magpie\System\Impls\Concepts;

use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\System\Concepts\DefaultProviderRegistrable;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessCommandLine;

/**
 * Provide support for process related functionalities
 * @internal
 */
interface ProcessSupportable extends DefaultProviderRegistrable
{
    /**
     * Create a new process using given command line
     * @param ProcessCommandLine $commandLine
     * @return Process
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    public function createProcess(ProcessCommandLine $commandLine) : Process;


    /**
     * Find the actual path of the PHP executable
     * @return string|null
     */
    public function getPhpPath() : ?string;
}