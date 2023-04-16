<?php

namespace Magpie\General\Contents;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\General\Concepts\FileSystemAccessible;
use Magpie\General\Concepts\PrimitiveBinaryContentable;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Concepts\StreamReadConvertible;
use Magpie\General\IOs\FileReadStream;

/**
 * File based (primitive, stream based) binary content
 */
class PrimitiveFileBinaryContent implements PrimitiveBinaryContentable, StreamReadConvertible, FileSystemAccessible
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
     * Constructor
     * @param string $path
     * @param string|null $mimeType
     * @param string|null $filename
     */
    public function __construct(string $path, ?string $mimeType = null, ?string $filename = null)
    {
        $this->path = $path;
        $this->mimeType = $mimeType;
        $this->filename = $filename;
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
        $stream = $this->getReadStream();

        $ret = '';
        while ($stream->hasData()) {
            $ret .= $stream->read();
        }

        return $ret;
    }


    /**
     * Convert into a simplified binary content
     * @return SimpleBinaryContent
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public function simplify() : SimpleBinaryContent
    {
        $data = $this->getData();
        return SimpleBinaryContent::create($data, $this->getMimeType(), $this->getFilename());
    }
}