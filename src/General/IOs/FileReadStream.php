<?php

namespace Magpie\General\IOs;

use Magpie\Exceptions\FileNotFoundException;
use Magpie\Exceptions\FileOperationFailedException;
use Magpie\Exceptions\StreamReadFailureException;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Concepts\Closeable;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\ReleaseOnDestruct;

/**
 * Read stream from file
 */
class FileReadStream implements StreamReadable, Releasable, Closeable
{
    use ReleaseOnDestruct;


    /**
     * @var resource|null Attached file resource
     */
    protected mixed $file;


    /**
     * Constructor
     * @param resource $file
     */
    protected function __construct(mixed $file)
    {
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
        if ($this->file === null) return;
        fclose($this->file);
    }


    /**
     * @inheritDoc
     */
    public function hasData() : bool
    {
        if ($this->file === null) return false;
        return !feof($this->file);
    }


    /**
     * @inheritDoc
     */
    public function read(?int $max = null) : string
    {
        if ($this->file === null) throw new StreamReadFailureException();

        $max = $max ?? StreamConstants::DEFAULT_CHUNK_SIZE;

        $data = fread($this->file, $max);
        if ($data === false) throw new StreamReadFailureException();

        return $data;
    }


    /**
     * Create file reading stream from given path
     * @param string $path
     * @return static
     * @throws FileNotFoundException
     * @throws FileOperationFailedException
     */
    public static function from(string $path) : static
    {
        if (static::isCheckPath($path) && !LocalRootFileSystem::instance()->isFileExist($path)) throw new FileNotFoundException($path);

        $file = PhpIo::fopen($path, 'r');
        return new static($file);
    }


    /**
     * Create file reading stream from given resource
     * @param resource $file
     * @return static
     * @internal
     */
    public static function _fromResource(mixed $file) : static
    {
        return new static($file);
    }


    /**
     * Is path checked
     * @param string $path
     * @return bool
     */
    protected static function isCheckPath(string $path) : bool
    {
        if (str_starts_with($path, 'file://')) return true;

        if (str_starts_with($path, 'zip://')) return false;
        if (str_starts_with($path, 'http://')) return false;
        if (str_starts_with($path, 'https://')) return false;

        return true;
    }
}