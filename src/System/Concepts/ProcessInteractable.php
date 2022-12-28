<?php

namespace Magpie\System\Concepts;

use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\StreamException;
use Magpie\System\Process\ProcessStandardStream;
use Magpie\System\Process\ProcessStandardStreamOutput;

/**
 * May interact with the process asynchronously
 */
interface ProcessInteractable
{
    /**
     * Poll for output contents from given process's stream
     * @param ProcessStandardStream $stream
     * @return iterable<string>
     * @throws InvalidStateException
     * @throws OperationFailedException
     * @throws StreamException
     */
    public function getOutputs(ProcessStandardStream $stream) : iterable;


    /**
     * Poll for any output contents from any process's stream
     * @return iterable<ProcessStandardStreamOutput>
     * @throws InvalidStateException
     * @throws OperationFailedException
     * @throws StreamException
     */
    public function getAnyOutputs() : iterable;


    /**
     * Wait until the current process is terminated (completes)
     * @return int Exit code received upon process termination
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    public function wait() : int;
}