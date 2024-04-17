<?php

namespace Magpie\Facades\FileSystem\Providers\Local;

use Closure;
use Magpie\General\Concepts\BinaryContentable;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\FileSystemAccessible;

/**
 * Lazy reader for local file system
 */
class LocalLazyBinaryContent implements BinaryDataProvidable, FileSystemAccessible
{
    /**
     * @var string The full path
     */
    protected readonly string $fullPath;
    /**
     * @var Closure Reader function
     */
    protected readonly Closure $readerFn;
    /**
     * @var BinaryDataProvidable|null Cached content data
     */
    protected ?BinaryDataProvidable $content = null;


    /**
     * Constructor
     * @param string $fullPath
     * @param callable():BinaryDataProvidable $readerFn
     */
    protected function __construct(string $fullPath, callable $readerFn)
    {
        $this->fullPath = $fullPath;
        $this->readerFn = $readerFn;
    }


    /**
     * @inheritDoc
     */
    public function getFileSystemPath() : string
    {
        return $this->fullPath;
    }


    /**
     * @inheritDoc
     */
    public function getData() : string
    {
        if ($this->content === null) $this->content = ($this->readerFn)();
        return $this->content->getData();
    }


    /**
     * Create an instance
     * @param string $fullPath
     * @param callable():BinaryContentable $readerFn
     * @return static
     * @internal
     */
    public static function _create(string $fullPath, callable $readerFn) : static
    {
        return new static($fullPath, $readerFn);
    }
}