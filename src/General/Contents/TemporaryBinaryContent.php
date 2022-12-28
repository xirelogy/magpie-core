<?php

namespace Magpie\General\Contents;

use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Concepts\BinaryContentable;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\FileSystemAccessible;
use Magpie\General\Concepts\Releasable;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\ReleaseOnDestruct;

/**
 * Temporary binary content
 */
class TemporaryBinaryContent implements BinaryContentable, FileSystemAccessible, Releasable
{
    use ReleaseOnDestruct;


    /**
     * @var string Temporary path storing the binary content
     */
    protected readonly string $path;
    /**
     * @var string|null Associated MIME type
     */
    protected readonly ?string $mimeType;
    /**
     * @var string|null Associated filename
     */
    protected readonly ?string $filename;
    /**
     * @var int Expected data size
     */
    protected readonly int $dataSize;
    /**
     * @var bool If the content is valid
     */
    protected bool $isValid;


    /**
     * Constructor
     * @param string $path
     * @param string|null $mimeType
     * @param string|null $filename
     * @param int $dataSize
     */
    protected function __construct(string $path, ?string $mimeType, ?string $filename, int $dataSize)
    {
        $this->path = $path;
        $this->mimeType = $mimeType;
        $this->filename = $filename;
        $this->dataSize = $dataSize;
        $this->isValid = true;
    }


    /**
     * @inheritDoc
     */
    public function release() : void
    {
        if (!$this->isValid) return;

        Excepts::noThrow(fn() => LocalRootFileSystem::instance()->deleteFile($this->path));

        $this->isValid = false;
    }


    /**
     * @inheritDoc
     */
    public function getFileSystemPath() : string
    {
        return $this->path;
    }


    /**
     * @inheritDoc
     */
    public function getMimeType() : ?string
    {
        return $this->mimeType;
    }


    /**
     * @inheritDoc
     */
    public function getFilename() : ?string
    {
        return $this->filename;
    }


    /**
     * @inheritDoc
     */
    public function getData() : string
    {
        return LocalRootFileSystem::instance()->readFile($this->path)->getData();
    }


    /**
     * @inheritDoc
     */
    public function getDataSize() : int
    {
        return $this->dataSize;
    }


    /**
     * Clone from other content
     * @param BinaryDataProvidable $content
     * @return static
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public static function fromContent(BinaryDataProvidable $content) : static
    {
        // Shortcut function
        if ($content instanceof static) return $content;

        $path = @tempnam(sys_get_temp_dir(), 'magpie_');
        if ($path === false) throw new OperationFailedException();

        $data = $content->getData();
        $dataSize = strlen($data);

        LocalRootFileSystem::instance()->writeFile($path, $data);

        if ($content instanceof BinaryContentable) {
            return new static($path, $content->getMimeType(), $content->getFilename(), $content->getDataSize());
        } else {
            return new static($path, null, null, strlen($dataSize));
        }

    }
}