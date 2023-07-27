<?php

namespace Magpie\System\Impls;

use Exception;
use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\OperationInterruptedException;
use Magpie\Exceptions\OperationTimeoutException;
use Magpie\Exceptions\StreamException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Concepts\StreamReadConvertible;
use Magpie\General\DateTimes\Duration;
use Magpie\General\DateTimes\Specific\DurationInNanoseconds;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessCommandLine;
use Magpie\System\Process\ProcessStandardStream;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessSignaledException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process as SymfonyProcessInterface;
use Throwable;

/**
 * Process representation using Symfony backend
 * @internal
 */
class SymfonyProcess extends Process
{
    /**
     * Input chunk size
     */
    protected const INPUT_CHUNK_SIZE = 512;

    /**
     * @var SymfonyProcessInterface Backend
     */
    protected readonly SymfonyProcessInterface $backend;


    /**
     * Constructor
     * @param ProcessCommandLine $commandLine
     */
    public function __construct(ProcessCommandLine $commandLine)
    {
        parent::__construct();

        $cwd = $commandLine->getWorkDir();
        $environment = $commandLine->getEnvironment();

        $this->backend = new SymfonyProcessInterface($commandLine->arguments, $cwd, $environment);
    }


    /**
     * @inheritDoc
     */
    public function release() : void
    {
        if ($this->backend->isRunning()) {
            $this->backend->stop();
        }
    }


    /**
     * @inheritDoc
     */
    public function isRunning() : bool
    {
        return $this->backend->isRunning();
    }


    /**
     * @inheritDoc
     */
    public function getPid() : ?int
    {
        return $this->backend->getPid();
    }


    /**
     * @inheritDoc
     */
    public function withInput(StreamReadable|StreamReadConvertible|string|null $input) : static
    {
        if ($input instanceof StreamReadConvertible) {
            $input = $input->getReadStream();
        }

        if ($input instanceof StreamReadable) {
            $this->backend->setInput(static::readFromStream($input));
            return $this;
        }

        if (is_string($input) || $input === null) {
            $this->backend->setInput($input);
            return $this;
        }

        throw new UnsupportedValueException($input);
    }


    /**
     * @inheritDoc
     */
    public function withTimeout(?Duration $timeout) : static
    {
        $backendTimeout = static::translateDuration($timeout);

        $this->backend->setTimeout($backendTimeout);

        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withTty(bool $isTty = true) : static
    {
        try {
            $this->backend->setTty($isTty);
        } catch (Throwable $ex) {
            throw new OperationFailedException(previous: $ex);
        }

        return $this;
    }


    /**
     * Read from a stream (i.e. translate StreamReadable into an iterable stream)
     * @param StreamReadable $input
     * @return iterable<string>
     * @throws StreamException
     */
    protected static function readFromStream(StreamReadable $input) : iterable
    {
        while ($input->hasData()) {
            yield $input->read(static::INPUT_CHUNK_SIZE);
        }
    }


    /**
     * @inheritDoc
     */
    public function start() : void
    {
        // Adapt the callback function
        $callback = function (string $type, string $text) : void {
            $stream = $type === SymfonyProcessInterface::OUT ? ProcessStandardStream::OUTPUT : ProcessStandardStream::ERROR;
            $this->onProcessOutputReceived($stream, $text);
        };

        // Handover to Symfony
        try {
            $this->backend->start($callback);
        } catch (RuntimeException $ex) {
            throw new InvalidStateException(previous: $ex);
        } catch (Exception $ex) {
            throw new OperationFailedException(previous: $ex);
        }
    }


    /**
     * @inheritDoc
     */
    public function stop() : ?int
    {
        try {
            return $this->backend->stop();
        } catch (RuntimeException $ex) {
            throw new InvalidStateException(previous: $ex);
        } catch (Exception $ex) {
            throw new OperationFailedException(previous: $ex);
        }
    }


    /**
     * @inheritDoc
     */
    protected function onWait() : int
    {
        if (!$this->isRunning()) throw new InvalidStateException();

        try {
            return $this->backend->wait();
        } catch (LogicException $ex) {
            throw new InvalidStateException(previous: $ex);
        } catch (ProcessTimedOutException $ex) {
            throw new OperationTimeoutException(previous: $ex);
        } catch (ProcessSignaledException $ex) {
            throw new OperationInterruptedException(previous: $ex);
        } catch (Exception $ex) {
            throw new OperationFailedException(previous: $ex);
        }
    }


    /**
     * @inheritDoc
     */
    protected function asyncCheckTerminated() : ?int
    {
        return $this->backend->getExitCode();
    }


    /**
     * Collect the exit code result
     * @return int|null
     */
    public function collectExitCode() : ?int
    {
        return $this->backend->getExitCode();
    }


    /**
     * Translate duration
     * @param Duration|null $value
     * @return float|null
     */
    protected static function translateDuration(?Duration $value) : ?float
    {
        if ($value === null) return null;

        if ($value->getPrecisionScale() >= 0) return $value->getSeconds();

        $nanoSeconds = $value->getValueAtPrecisionScale(DurationInNanoseconds::SCALE);
        return floatval($nanoSeconds) / 1000000000;
    }
}