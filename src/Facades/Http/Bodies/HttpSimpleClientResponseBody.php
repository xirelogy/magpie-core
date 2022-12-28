<?php

namespace Magpie\Facades\Http\Bodies;

use Magpie\Facades\Http\HttpClientResponseBody;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\IOs\SimpleReadStream;
use Magpie\General\Packs\PackContext;

/**
 * Simple HTTP client response body
 */
class HttpSimpleClientResponseBody extends HttpClientResponseBody
{
    /**
     * Maximum length during pack (default)
     */
    public const DEFAULT_MAX_PACKED_LENGTH = 256;

    /**
     * @var string String content
     */
    protected string $content;
    /**
     * @var int Maximum length during pack
     */
    protected int $maxPackLength;


    /**
     * Constructor
     * @param string $content
     * @param int|null $maxPackLength
     */
    protected function __construct(string $content, ?int $maxPackLength)
    {
        $this->content = $content;
        $this->maxPackLength = $maxPackLength ?? static::DEFAULT_MAX_PACKED_LENGTH;
    }


    /**
     * @inheritDoc
     */
    public function getData() : string
    {
        return $this->content;
    }


    /**
     * @inheritDoc
     */
    public function getReadStream() : StreamReadable
    {
        return new SimpleReadStream($this->content);
    }


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        parent::onPack($ret, $context);

        $data = $this->getData();
        $dataLength = strlen($data);

        if ($dataLength <= $this->maxPackLength) {
            $ret->data = $data;
        } else {
            $ret->partialData = substr($data, 0, $this->maxPackLength);
            $ret->dataLength = $dataLength;
        }
    }


    /**
     * Create from content
     * @param string $content
     * @param int|null $maxPackLength
     * @return static
     */
    public static function fromContent(string $content, ?int $maxPackLength = null) : static
    {
        return new static($content, $maxPackLength);
    }
}