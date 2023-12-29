<?php

namespace Magpie\System\Impls;

use Closure;
use Exception;
use Fiber;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\StreamException;
use Magpie\Facades\Random;
use Magpie\General\Arr;
use Magpie\General\Concepts\Dispatchable;
use Magpie\General\Randoms\RandomCharset;
use Magpie\General\Sugars\Excepts;
use Magpie\System\Kernel\EasyFiber;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessStandardStream;
use Magpie\System\Process\ProcessStandardStreamOutput;

/**
 * Representation of an asynchronous process's
 * @internal
 */
class ProcessAsyncHandle implements Dispatchable
{
    /**
     * @var string Unique identity
     */
    public readonly string $id;
    /**
     * @var Process Associated process
     */
    public readonly Process $process;
    /**
     * @var Closure Functionality to check if process is terminated
     */
    protected readonly Closure $checkTerminateFn;
    /**
     * @var array<ProcessStandardStreamOutput> $outputs;
     */
    protected array $outputs = [];
    /**
     * @var int|null Exit code upon process termination
     */
    protected ?int $exitCode = null;
    /**
     * @var Exception|null Any pending exceptions
     */
    protected ?Exception $pendingException = null;
    /**
     * @var array<string, array<Fiber>> All fibers listening for output content
     */
    protected array $outputFibers = [];
    /**
     * @var Fiber|null Waiting fiber
     */
    protected ?Fiber $waitFiber = null;


    /**
     * Constructor
     * @param Process $process
     * @param callable():(int|null) $checkTerminateFn
     */
    public function __construct(Process $process, callable $checkTerminateFn)
    {
        $this->id = Random::string(16, RandomCharset::LOWER_ALPHANUM);
        $this->process = $process;
        $this->checkTerminateFn = $checkTerminateFn;
    }


    /**
     * Register current handle to poll
     * @return void
     */
    public function register() : void
    {
        ProcessAsyncPoll::instance()->registerHandle($this);
    }


    /**
     * Deregister current handle from poll
     * @return void
     */
    public function deregister() : void
    {
        ProcessAsyncPoll::instance()->deregisterHandle($this);
    }


    /**
     * Subscribe to output events for given fiber
     * @param Fiber $fiber
     * @param ProcessStandardStream|null $stream
     * @return void
     */
    public function asyncSubscribeOutput(Fiber $fiber, ?ProcessStandardStream $stream) : void
    {
        foreach (static::getListeningStreams($stream) as $listenStream) {
            $fibers = $this->outputFibers[$listenStream->value] ?? [];
            if (in_array($fiber, $fibers)) continue;
            $fibers[] = $fiber;
            $this->outputFibers[$listenStream->value] = $fibers;
        }
    }


    /**
     * Unsubscribe from output events for given fiber
     * @param Fiber $fiber
     * @param ProcessStandardStream|null $stream
     * @return void
     */
    public function asyncUnsubscribeOutput(Fiber $fiber, ?ProcessStandardStream $stream) : void
    {
        foreach (static::getListeningStreams($stream) as $listenStream) {
            $fibers = $this->outputFibers[$listenStream->value] ?? [];
            Arr::deleteByValue($fibers, $fiber);
            $this->outputFibers[$listenStream->value] = $fibers;
        }
    }


    /**
     * Get next output
     * @return ProcessStandardStreamOutput|null
     * @throws InvalidStateException
     * @throws OperationFailedException
     * @throws StreamException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function asyncGetOutput() : ?ProcessStandardStreamOutput
    {
        try {
            $next = EasyFiber::suspend();
            return $next instanceof ProcessStandardStreamOutput ? $next : null;
        } catch (InvalidStateException|OperationFailedException|StreamException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new OperationFailedException(previous: $ex);
        }
    }


    /**
     * Corresponding listening streams for given stream specification
     * @param ProcessStandardStream|null $stream
     * @return iterable<ProcessStandardStream>
     */
    protected static function getListeningStreams(?ProcessStandardStream $stream) : iterable
    {
        if ($stream !== null) {
            yield $stream;
        } else {
            yield ProcessStandardStream::OUTPUT;
            yield ProcessStandardStream::ERROR;
        }
    }


    /**
     * Wait for exit state
     * @param Fiber $fiber
     * @return int
     * @throws InvalidStateException
     * @throws OperationFailedException
     * @noinspection PhpDocRedundantThrowsInspection
     */
    public function asyncWait(Fiber $fiber) : int
    {
        $this->waitFiber = $fiber;

        try {
            while (true) {
                $next = EasyFiber::suspend();
                if ($next === null && $this->exitCode !== null) return $this->exitCode;
            }
        } catch (InvalidStateException|OperationFailedException $ex) {
            throw $ex;
        } catch (Exception $ex) {
            throw new OperationFailedException(previous: $ex);
        } finally {
            $this->waitFiber = null;
        }
    }


    /**
     * @inheritDoc
     */
    public function dispatch() : void
    {
        if ($this->pendingException !== null) {
            $captureException = $this->pendingException;
            $this->pendingException = null;
            $this->dispatchException($captureException);
        }

        while (count($this->outputs) > 0) {
            $output = $this->outputs[0];
            array_shift($this->outputs);

            $this->dispatchOutput($output);
        }

        if ($this->exitCode !== null) {
            $this->dispatchExit();
        }
    }


    /**
     * Dispatch output to all listening fibers
     * @param ProcessStandardStreamOutput $output
     * @return void
     */
    protected function dispatchOutput(ProcessStandardStreamOutput $output) : void
    {
        $fibers = $this->outputFibers[$output->stream->value] ?? [];
        foreach ($fibers as $fiber) {
            Excepts::noThrow(fn () => EasyFiber::resume($fiber, $output));
        }
    }


    /**
     * Dispatch exceptions
     * @param Exception $ex
     * @return void
     */
    protected function dispatchException(Exception $ex) : void
    {
        foreach (static::getListeningStreams(null) as $stream) {
            $fibers = $this->outputFibers[$stream->value] ?? [];
            foreach ($fibers as $fiber) {
                Excepts::noThrow(fn () => EasyFiber::throw($fiber, $ex));
            }
        }

        if ($this->waitFiber !== null) {
            Excepts::noThrow(fn () => EasyFiber::throw($this->waitFiber, $ex));
        }
    }


    /**
     * Dispatch exit signal
     * @return void
     */
    protected function dispatchExit() : void
    {
        foreach (static::getListeningStreams(null) as $stream) {
            $fibers = $this->outputFibers[$stream->value] ?? [];
            foreach ($fibers as $fiber) {
                Excepts::noThrow(fn () => EasyFiber::resume($fiber, null));
            }
        }

        if ($this->waitFiber !== null) {
            Excepts::noThrow(fn () => EasyFiber::resume($this->waitFiber, null));
        }
    }


    /**
     * Check if current handle is dispatchable
     * @return Dispatchable|null
     */
    public function checkDispatchable() : ?Dispatchable
    {
        // Where there is output
        if (count($this->outputs) > 0) return $this;

        // Attempt to collect exit code
        if ($this->exitCode === null) {
            try {
                $this->exitCode = ($this->checkTerminateFn)();
            } catch (Exception $ex) {
                $this->pendingException = $ex;
                return $this;
            }
        }

        // When the process exited
        if ($this->exitCode !== null) return $this;

        return null;
    }


    /**
     * Queue an output received to the handle
     * @param ProcessStandardStreamOutput $output
     * @return void
     */
    public function queueOutput(ProcessStandardStreamOutput $output) : void
    {
        $this->outputs[] = $output;
    }
}