<?php

namespace Magpie\System\Process;

use Exception;
use Fiber;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Concepts\StreamReadConvertible;
use Magpie\General\DateTimes\Duration;
use Magpie\General\IOs\TemporaryWriteStream;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\ReleaseOnDestruct;
use Magpie\System\Concepts\ProcessInteractable;
use Magpie\System\Concepts\ProcessOutputCollectable;
use Magpie\System\Impls\ProcessAsyncHandle;
use Magpie\System\Impls\ProcessSupport;
use Magpie\System\Traits\NonSerializable;

/**
 * Representation of an execution process
 */
abstract class Process implements Releasable
{
    use ReleaseOnDestruct;
    use NonSerializable;

    /**
     * @var ProcessOutputCollectable Collector for process output
     */
    protected ProcessOutputCollectable $outputCollector;


    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->outputCollector = new BufferedProcessOutputCollector();
    }


    /**
     * If current process is running
     * @return bool
     */
    public abstract function isRunning() : bool;


    /**
     * The process ID
     * @return int|null
     */
    public abstract function getPid() : ?int;


    /**
     * Specify the input to the process when running
     * @param StreamReadable|StreamReadConvertible|string|null $input
     * @return $this
     * @throws Exception
     */
    public abstract function withInput(StreamReadable|StreamReadConvertible|string|null $input) : static;


    /**
     * Specify the output collector to receive output from the process
     * @param ProcessOutputCollectable $collector
     * @return $this
     */
    public final function withOutput(ProcessOutputCollectable $collector) : static
    {
        $this->outputCollector = $collector;
        $this->onSetOutput($collector);
        return $this;
    }


    /**
     * Handle the output collector to receive output from the process
     * @param ProcessOutputCollectable $collector
     * @return void
     */
    protected function onSetOutput(ProcessOutputCollectable $collector) : void
    {
        // Default NOP
    }


    /**
     * Specify the process timeout
     * @param Duration|null $timeout
     * @return $this
     */
    public abstract function withTimeout(?Duration $timeout) : static;


    /**
     * Specify the process to enable TTY mode
     * @param bool $isTty
     * @return $this
     * @throws SafetyCommonException
     */
    public abstract function withTty(bool $isTty = true) : static;


    /**
     * Run the process synchronously
     * @return int Exit code received upon process termination
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    public final function run() : int
    {
        $this->start();

        return $this->wait();
    }


    /**
     * Run the process asynchronously
     * @return ProcessInteractable
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    public final function runAsync() : ProcessInteractable
    {
        $onTerminateFn = function () : ?int {
            return $this->asyncCheckTerminated();
        };

        $async = new ProcessAsyncHandle($this, $onTerminateFn);
        $subCollector = new StreamedProcessOutputCollector(TemporaryWriteStream::create(), TemporaryWriteStream::create());

        // Create the interactive output collector
        $interactiveOutput = new class($subCollector, $async) implements ProcessOutputCollectable {
            /**
             * Constructor
             * @param ProcessOutputCollectable $subCollector
             * @param ProcessAsyncHandle $async
             */
            public function __construct(
                protected ProcessOutputCollectable $subCollector,
                protected ProcessAsyncHandle $async,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function close() : void
            {
                $this->subCollector->close();
            }


            /**
             * @inheritDoc
             */
            public function receive(ProcessStandardStream $stream, string $content) : void
            {
                $this->async->queueOutput(new ProcessStandardStreamOutput($stream, $content));
                $this->subCollector->receive($stream, $content);
            }


            /**
             * @inheritDoc
             */
            public function export(ProcessStandardStream $stream) : StreamReadable|null
            {
                return $this->subCollector->export($stream);
            }
        };

        // Setup and start the process
        $this->withOutput($interactiveOutput);
        $this->start();
        $async->register();

        // Return the interactive handle
        return new class($async) implements ProcessInteractable {
            /**
             * Constructor
             * @param ProcessAsyncHandle $async
             */
            public function __construct(
                protected ProcessAsyncHandle $async,
            ) {

            }


            /**
             * Destructor
             */
            public function __destruct()
            {
                $this->async->deregister();
            }


            /**
             * @inheritDoc
             */
            public function getOutputs(ProcessStandardStream $stream) : iterable
            {
                $fiber = Fiber::getCurrent();
                if ($fiber === null) throw new InvalidStateException();

                $this->async->asyncSubscribeOutput($fiber, $stream);

                try {
                    while (true) {
                        $nextOutput = $this->async->asyncGetOutput();
                        if ($nextOutput === null) return;

                        yield $nextOutput->content;
                    }
                } finally {
                    $this->async->asyncUnsubscribeOutput($fiber, $stream);
                }
            }


            /**
             * @inheritDoc
             */
            public function getAnyOutputs() : iterable
            {
                $fiber = Fiber::getCurrent();
                if ($fiber === null) throw new InvalidStateException();

                $this->async->asyncSubscribeOutput($fiber, null);

                try {
                    while (true) {
                        $nextOutput = $this->async->asyncGetOutput();
                        if ($nextOutput === null) return;

                        yield $nextOutput;
                    }
                } finally {
                    $this->async->asyncUnsubscribeOutput($fiber, null);
                }
            }


            /**
             * @inheritDoc
             */
            public function wait() : int
            {
                $fiber = Fiber::getCurrent();
                if ($fiber === null) throw new InvalidStateException();

                return $this->async->asyncWait($fiber);
            }
        };
    }


    /**
     * Start the process
     * @return void
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    public abstract function start() : void;


    /**
     * Stop the process
     * @return int|null Exit code received upon process termination
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    public abstract function stop() : ?int;


    /**
     * Wait until the current process is terminated (completes)
     * @return int Exit code received upon process termination
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    public final function wait() : int
    {
        $exitCode = $this->onWait();
        $this->onProcessTerminated();

        return $exitCode;
    }


    /**
     * Wait until the current process is terminated (completes)
     * @return int Exit code received upon process termination
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    protected abstract function onWait() : int;


    /**
     * Check asynchronously if the process is terminated
     * @return int|null Exit code received, if process is terminated
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    protected abstract function asyncCheckTerminated() : ?int;


    /**
     * Get notification that process is terminated
     * @return void
     */
    protected function onProcessTerminated() : void
    {
        Excepts::noThrow(fn () => $this->outputCollector->close());
    }


    /**
     * Collect the exit code result
     * @return int|null
     */
    public abstract function collectExitCode() : ?int;


    /**
     * Collect the result received from the process's 'output' stream
     * @return StreamReadable|StreamReadConvertible|iterable<string>|string|null
     */
    public final function collectOutput() : StreamReadable|StreamReadConvertible|iterable|string|null
    {
        return $this->outputCollector->export(ProcessStandardStream::OUTPUT);
    }


    /**
     * Collect the result received from the process's 'error' stream
     * @return StreamReadable|StreamReadConvertible|iterable<string>|string|null
     */
    public final function collectError() : StreamReadable|StreamReadConvertible|iterable|string|null
    {
        return $this->outputCollector->export(ProcessStandardStream::ERROR);
    }


    /**
     * Handle output received from process
     * @param ProcessStandardStream $stream
     * @param string $content
     * @return void
     */
    protected final function onProcessOutputReceived(ProcessStandardStream $stream, string $content) : void
    {
        $this->outputCollector->receive($stream, $content);
    }


    /**
     * Create process from given command line
     * @param ProcessCommandLine $commandLine
     * @return static
     * @throws InvalidStateException
     * @throws OperationFailedException
     */
    public static function fromCommandLine(ProcessCommandLine $commandLine) : static
    {
        return ProcessSupport::createProcess($commandLine);
    }
}