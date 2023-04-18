<?php

namespace Magpie\Facades\FileSystem\Providers\Local;

use Magpie\Exceptions\FileOperationFailedException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\PathTargetReadable;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Contexts\Scoped;
use Magpie\General\IOs\FileReadStream;
use Throwable;

/**
 * Read target to read from local file
 */
class LocalFileReadTarget extends LocalScopedTarget implements PathTargetReadable
{
    /**
     * @var string Target path
     */
    public readonly string $path;


    /**
     * Constructor
     * @param string $path
     * @param callable():iterable<Scoped>|null $getScopesFn
     * @noinspection PhpDocSignatureInspection
     */
    public function __construct(string $path, ?callable $getScopesFn = null)
    {
        parent::__construct($getScopesFn);

        $this->path = $path;
    }


    /**
     * @inheritDoc
     */
    public function getPath() : string
    {
        return $this->path;
    }


    /**
     * @inheritDoc
     */
    public function createStream() : StreamReadable
    {
        try {
            return FileReadStream::from($this->path);
        } catch (SafetyCommonException $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new FileOperationFailedException($this->path, previous: $ex);
        }
    }
}