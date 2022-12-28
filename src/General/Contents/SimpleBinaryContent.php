<?php

namespace Magpie\General\Contents;

use Exception;
use Magpie\Exceptions\InvalidDataException;
use Magpie\General\Concepts\BinaryContentable;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;

/**
 * Simple binary content
 */
class SimpleBinaryContent implements BinaryContentable, Packable
{
    use CommonPackable;


    /**
     * @var string|null Associated MIME type
     */
    public ?string $mimeType;
    /**
     * @var string|null Associated filename
     */
    public ?string $filename;
    /**
     * @var string Stored data
     */
    public string $data;


    /**
     * Constructor
     * @param string|null $mimeType
     * @param string|null $filename
     * @param string $data
     */
    protected function __construct(?string $mimeType, ?string $filename, string $data)
    {
        $this->mimeType = $mimeType;
        $this->filename = $filename;
        $this->data = $data;
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
        return $this->data;
    }


    /**
     * @inheritDoc
     */
    public function getDataSize() : int
    {
        return strlen($this->data);
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->filename = $this->getFilename();
        $ret->mimeType = $this->getMimeType();
        $ret->dataSize = $this->getDataSize();
    }


    /**
     * Create a new content
     * @param string $data
     * @param string|null $mimeType
     * @param string|null $filename
     * @return static
     */
    public static function create(string $data, ?string $mimeType = null, ?string $filename = null) : static
    {
        return new static($mimeType, $filename, $data);
    }


    /**
     * Create new content by decoding from data-URL
     * @param string $text
     * @param string|null $filename
     * @return static
     * @throws Exception
     */
    public static function fromDataUrl(string $text, ?string $filename = null) : static
    {
        if (!str_starts_with($text, 'data:')) throw new InvalidDataException();

        $data = substr($text, 5);
        $commaPos = strpos($text, ',');
        if ($commaPos === false) throw new InvalidDataException();

        $meta = substr($data, 0, $commaPos);
        $payload = substr($data, $commaPos + 1);

        // Check if this is base64 encoded
        $isBase64 = false;
        if (str_ends_with($meta, ';base64')) {
            $isBase64 = true;
            $meta = substr($meta, 0, -7);
        }

        // Derive MIME type
        $mimeType = !is_empty_string($meta) ? $meta : 'text/plain';

        // Process the payload
        if ($isBase64) {
            $payload = @base64_decode($payload, true);
            if ($payload === false) throw new InvalidDataException();
        }

        return new static($mimeType, $filename, $payload);
    }
}