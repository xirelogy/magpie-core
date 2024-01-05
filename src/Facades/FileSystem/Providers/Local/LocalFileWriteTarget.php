<?php

namespace Magpie\Facades\FileSystem\Providers\Local;

use Magpie\Exceptions\FileOperationFailedException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\StreamWriteable;
use Magpie\General\Concepts\TargetWritable;
use Magpie\General\Contexts\Scoped;
use Magpie\General\IOs\FileWriteStream;
use Throwable;

/**
 * Write target to write to local file
 */
class LocalFileWriteTarget extends LocalScopedTarget implements TargetWritable
{
    /**
     * @var string Target path
     */
    public readonly string $path;


    /**
     * Constructor
     * @param string $path
     * @param callable():iterable<Scoped>|null $getScopesFn
     */
    public function __construct(string $path, ?callable $getScopesFn = null)
    {
        parent::__construct($getScopesFn);

        $this->path = $path;
    }


    /**
     * @inheritDoc
     */
    public function createStream() : StreamWriteable
    {
        try {
            return FileWriteStream::createForPath($this->path);
        } catch (SafetyCommonException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new FileOperationFailedException($this->path, previous: $ex);
        }
    }
}