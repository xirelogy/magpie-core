<?php

namespace Magpie\General\Contents;

use Magpie\Exceptions\FileNotFoundException;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Concepts\BinaryContentable;
use Magpie\General\FilePath;

/**
 * File based binary content
 */
class FileBinaryContent extends PrimitiveFileBinaryContent implements BinaryContentable
{
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
        parent::__construct($path, $mimeType, $filename);

        $this->dataSize = $dataSize;
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