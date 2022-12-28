<?php

namespace Magpie\Facades\Http\Bodies;

use Magpie\Codecs\Formats\Formatter;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Names\CommonMimeType;
use Magpie\General\Simples\SimpleXML;

/**
 * A body to be sent along with the request, encoded in XML
 */
class HttpXmlClientRequestBody extends HttpEncodedClientRequestBody
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'xml';

    /**
     * @var object Associated payload
     */
    public readonly object $payload;
    /**
     * @var string Encoded content
     */
    protected readonly string $encoded;


    /**
     * Constructor
     * @param object $payload
     * @param string $rootElementName
     * @param Formatter|null $formatter
     * @throws SafetyCommonException
     */
    public function __construct(object $payload, string $rootElementName = SimpleXML::DEFAULT_ROOT_NAME, ?Formatter $formatter = null)
    {
        $this->payload = $payload;

        if ($formatter !== null) $payload = $formatter->format($payload);
        $this->encoded = SimpleXML::encode($payload, $rootElementName);
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
        return CommonMimeType::XML;
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