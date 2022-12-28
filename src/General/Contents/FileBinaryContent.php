<?php

namespace Magpie\General\Contents;

use Exception;
use Magpie\Exceptions\FileNotFoundException;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Concepts\BinaryContentable;
use Magpie\General\Concepts\FileSystemAccessible;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Concepts\StreamReadConvertible;
use Magpie\General\FilePath;
use Magpie\General\IOs\FileReadStream;

/**
 * File based binary content
 */
class FileBinaryContent implements BinaryContentable, StreamReadConvertible, FileSystemAccessible
{
    /**
     * @var string The file path
     */
    protected string $path;
    /**
     * @var string|null Associated MIME type
     */
    protected ?string $mimeType;
    /**
     * @var string|null Associated filename
     */
    protected ?string $filename;
    /**
     * @var int File size
     */
    protected int $dataSize;


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
    public function getReadStream() : StreamReadable
    {
        return FileReadStream::from($this->path);
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
     * Convert into a simplified binary content
     * @return SimpleBinaryContent
     * @throws Exception
     */
    public function simplify() : SimpleBinaryContent
    {
        $data = $this->getData();
        return SimpleBinaryContent::create($data, $this->getMimeType(), $this->getFilename());
    }


    /**
     * Create new content by defining from file
     * @param string $path
     * @return static
     * @throws FileNotFoundException
     */
    public static function from(string $path) : static
    {
        if (!LocalRootFileSystem::instance()->isFileExist($path)) throw new FileNotFoundException($path);

        $filename = FilePath::getFilename($path);
        $mimeType = mime_content_type($path);
        if ($mimeType === false) $mimeType = null;

        $dataSize = filesize($path);
        if ($dataSize === false) throw new FileNotFoundException($path);

        return new static($path, $mimeType, $filename, $dataSize);
    }
}