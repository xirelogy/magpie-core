<?php

namespace Magpie\General\IOs;

use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\StreamWriteFailureException;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Concepts\StreamWriteFinalizable;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\ReleaseOnDestruct;

/**
 * Write stream into a temporary location
 */
final class TemporaryWriteStream implements StreamWriteFinalizable, Releasable
{
    use ReleaseOnDestruct;

    /**
     * @var resource|null Temporary 'file' resource handle
     */
    protected mixed $handle;


    /**
     * Constructor
     * @param resource $handle
     */
    protected function __construct(mixed $handle)
    {
        $this->handle = $handle;
    }


    /**
     * @inheritDoc
     */
    public function release() : void
    {
        Excepts::noThrow(fn () => $this->close());
    }


    /**
     * @inheritDoc
     */
    public function close() : void
    {
        if ($this->handle !== null) fclose($this->handle);
        $this->handle = null;
    }


    /**
     * @inheritDoc
     */
    public function write(string $data) : int
    {
        if ($this->handle === null) throw new StreamWriteFailureException();

        $ret = fwrite($this->handle, $data);
        if ($ret === false) throw new StreamWriteFailureException();

        return $ret;
    }


    /**
     * Access to the underlying resource handle
     * @return resource|null
     */
    public function getResourceHandle() : mixed
    {
        return $this->handle;
    }


    /**
     * @inheritDoc
     */
    public function finalize() : StreamReadable
    {
        if ($this->handle === null) throw new StreamWriteFailureException();

        // Transfer handle into local variable
        $readHandle = $this->handle;
        $this->handle = null;

        // Rewind and wrap
        rewind($readHandle);
        return FileReadStream::_fromResource($readHandle);
    }


    /**
     * Create a new temporary write stream
     * @return static
     * @throws OperationFailedException
     */
    public static function create() : static
    {
        $handle = @fopen('php://temp', 'w+');
        if ($handle === false) throw new OperationFailedException();

        return new static($handle);
    }
}