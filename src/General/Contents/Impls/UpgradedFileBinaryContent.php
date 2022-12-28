<?php

namespace Magpie\General\Contents\Impls;

use Magpie\General\Concepts\BinaryContentable;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\FileSystemAccessible;
use Magpie\General\FilePath;
use Magpie\General\Sugars\Excepts;

/**
 * Upgraded binary content from simple BinaryDataProvidable
 * @internal 
 */
class UpgradedFileBinaryContent implements BinaryContentable, FileSystemAccessible
{
    /**
     * @var BinaryDataProvidable&FileSystemAccessible Base content
     */
    protected BinaryDataProvidable&FileSystemAccessible $content;


    /**
     * Constructor
     * @param BinaryDataProvidable&FileSystemAccessible $content
     */
    public function __construct(BinaryDataProvidable&FileSystemAccessible $content)
    {
        $this->content = $content;
    }


    /**
     * @inheritDoc
     */
    public function getMimeType() : ?string
    {
        return null;
    }


    /**
     * @inheritDoc
     */
    public function getFilename() : ?string
    {
        return FilePath::getFilename($this->getFileSystemPath());
    }


    /**
     * @inheritDoc
     */
    public function getFileSystemPath() : string
    {
        return $this->content->getFileSystemPath();
    }


    /**
     * @inheritDoc
     */
    public function getData() : string
    {
        return $this->content->getData();
    }


    /**
     * @inheritDoc
     */
    public function getDataSize() : int
    {
        return Excepts::noThrow(fn () => strlen($this->content->getData()), 0);
    }
}