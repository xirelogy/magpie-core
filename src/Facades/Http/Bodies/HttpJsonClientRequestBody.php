<?php

namespace Magpie\Facades\Http\Bodies;

use Magpie\Codecs\Formats\Formatter;
use Magpie\Exceptions\InvalidJsonDataFormatException;
use Magpie\General\Names\CommonMimeType;
use Magpie\General\Simples\SimpleJSON;

/**
 * A body to be sent along with the request, encoded in JSON
 */
class HttpJsonClientRequestBody extends HttpEncodedClientRequestBody
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'json';

    /**
     * @var object|array Associated payload
     */
    public readonly object|array $payload;
    /**
     * @var string Encoded content
     */
    protected readonly string $encoded;


    /**
     * Constructor
     * @param object|array $payload
     * @param Formatter|null $formatter
     * @throws InvalidJsonDataFormatException
     */
    public function __construct(object|array $payload, ?Formatter $formatter = null)
    {
        $this->payload = $payload;

        if ($formatter !== null) $payload = $formatter->format($payload);
        $this->encoded = SimpleJSON::encode($payload);
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function getContentType() : string
    {
        return CommonMimeType::JSON;
    }


    /**
     * @inheritDoc
     */
    public function getContentLength() : ?int
    {
        return strlen($this->encoded);
    }


    /**
     * @inheritDoc
     */
    public function getEncodedBody() : string
    {
        return $this->encoded;
    }
}