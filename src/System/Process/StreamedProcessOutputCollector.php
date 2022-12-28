<?php

namespace Magpie\System\Process;

use Exception;
use Magpie\Exceptions\StreamException;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Concepts\StreamWriteable;
use Magpie\General\Concepts\StreamWriteConvertible;
use Magpie\General\Concepts\StreamWriteFinalizable;
use Magpie\System\Concepts\ProcessOutputCollectable;

/**
 * Receive and collect process output by redirecting them to streams
 */
class StreamedProcessOutputCollector implements ProcessOutputCollectable
{
    /**
     * @var bool If collector may receive
     */
    protected bool $isReceiving = true;
    /**
     * @var StreamWriteable|null Output receiver stream
     */
    protected ?StreamWriteable $outputStream;
    /**
     * @var StreamWriteable|null Error receiver stream
     */
    protected ?StreamWriteable $errorStream;
    /**
     * @var StreamReadable|null Exportable 'output' read stream
     */
    protected ?StreamReadable $outputReadStream = null;
    /**
     * @var StreamReadable|null Exportable 'error' read stream
     */
    protected ?StreamReadable $errorReadStream = null;


    /**
     * Constructor
     * @param StreamWriteable|StreamWriteConvertible|null $outputStream
     * @param StreamWriteable|StreamWriteConvertible|null $errorStream
     */
    public function __construct(StreamWriteable|StreamWriteConvertible|null $outputStream, StreamWriteable|StreamWriteConvertible|null $errorStream = null)
    {
        $this->outputStream = static::acceptStream($outputStream);
        $this->errorStream = static::acceptStream($errorStream);
    }


    /**
     * @inheritDoc
     */
    public function close() : void
    {
        if (!$this->isReceiving) return;

        $this->outputReadStream = static::closeStream($this->outputStream);
        $this->errorReadStream = static::closeStream($this->errorStream);

        $this->isReceiving = false;
    }


    /**
     * @inheritDoc
     */
    public function receive(ProcessStandardStream $stream, string $content) : void
    {
        try {
            switch ($stream) {
                case ProcessStandardStream::OUTPUT:
                    $this->outputStream?->write($content);
                    break;
                case ProcessStandardStream::ERROR:
                    $this->errorStream?->write($content);
                    break;
                default:
                    // NOP
                    break;
            }
        } catch (StreamException) {
            // Ignore exceptions
        }
    }


    /**
     * @inheritDoc
     */
    public function export(ProcessStandardStream $stream) : StreamReadable|null
    {
        return match ($stream) {
            ProcessStandardStream::OUTPUT => $this->outputReadStream,
            ProcessStandardStream::ERROR => $this->errorReadStream,
            default => null,
        };
    }


    /**
     * Accept and translate a stream argument
     * @param StreamWriteable|StreamWriteConvertible|null $stream
     * @return StreamWriteable|null
     */
    protected static function acceptStream(StreamWriteable|StreamWriteConvertible|null $stream) : ?StreamWriteable
    {
        if ($stream instanceof StreamWriteable) return $stream;
        if ($stream instanceof StreamWriteConvertible) return $stream->getWriteStream();
        return null;
    }


    /**
     * Close a stream
     * @param StreamWriteable|null $stream
     * @return StreamReadable|null
     * @throws Exception
     */
    protected static function closeStream(?StreamWriteable $stream) : ?StreamReadable
    {
        if ($stream === null) return null;

        if ($stream instanceof StreamWriteFinalizable) return $stream->finalize();

        // Default: close and nothing returned
        $stream->close();
        return null;
    }
}