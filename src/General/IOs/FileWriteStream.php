<?php

namespace Magpie\General\IOs;

use Exception;
use Magpie\Exceptions\FileOperationFailedException;
use Magpie\Exceptions\StreamWriteFailureException;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Concepts\StreamWriteFinalizable;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\ReleaseOnDestruct;

/**
 * Write stream into file
 */
class FileWriteStream implements StreamWriteFinalizable, Releasable
{
    use ReleaseOnDestruct;


    /**
     * @var string Associated file path
     */
    public readonly string $path;
    /**
     * @var resource Attached file resource
     */
    protected mixed $file;


    /**
     * Constructor
     * @param string $path
     * @param resource $file
     */
    protected function __construct(string $path, mixed $file)
    {
        $this->path = $path;
        $this->file = $file;
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
        if ($this->file !== null) {
            fclose($this->file);
        }

        $this->file = null;
    }


    /**
     * @inheritDoc
     */
    public function write(string $data) : int
    {
        if ($this->file === null) throw new StreamWriteFailureException();

        $ret = fwrite($this->file, $data);
        if ($ret === false) throw new StreamWriteFailureException();

        return $ret;
    }


    /**
     * @inheritDoc
     */
    public function finalize() : StreamReadable
    {
        if ($this->file === null) throw new StreamWriteFailureException();

        // Transfer handle into local variable
        $file = $this->file;
        $this->file = null;

        // Rewind and wrap
        rewind($file);
        return FileReadStream::_fromResource($file);
    }


    /**
     * Create to be saved to specific path
     * @param string $path
     * @return static
     * @throws Exception
     */
    public static function createForPath(string $path) : static
    {
        $file = PhpIo::fopen($path, 'w+');
        return new static($path, $file);
    }
}