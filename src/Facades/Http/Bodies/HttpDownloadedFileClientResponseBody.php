<?php

namespace Magpie\Facades\Http\Bodies;

use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Contents\FileBinaryContent;
use Magpie\General\Packs\PackContext;

/**
 * Downloaded file HTTP client response body
 */
class HttpDownloadedFileClientResponseBody extends HttpFileClientResponseBody
{
    /**
     * @var FileBinaryContent Downloaded content
     */
    protected FileBinaryContent $fileContent;


    /**
     * Constructor
     * @param FileBinaryContent $fileContent
     */
    public function __construct(FileBinaryContent $fileContent)
    {
        $this->fileContent = $fileContent;
    }


    /**
     * @inheritDoc
     */
    public function getFilename() : ?string
    {
        return $this->fileContent->getFilename();
    }


    /**
     * @inheritDoc
     */
    public function getMimeType() : ?string
    {
        return $this->fileContent->getMimeType();
    }


    /**
     * @inheritDoc
     */
    public function getData() : string
    {
        return $this->fileContent->getData();
    }


    /**
     * @inheritDoc
     */
    public function getDataSize() : int
    {
        return $this->fileContent->getDataSize();
    }


    /**
     * @inheritDoc
     */
    public function getReadStream() : StreamReadable
    {
        return $this->fileContent->getReadStream();
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $ret->dataSize = $this->getDataSize();
    }
}