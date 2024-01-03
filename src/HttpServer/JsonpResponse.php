<?php

namespace Magpie\HttpServer;

use Magpie\Exceptions\InvalidJsonDataFormatException;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonMimeType;
use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Sugars\Quote;

/**
 * Representation of a JSONP response
 */
class JsonpResponse extends Response
{
    /**
     * Constructor
     * @param string $callbackName
     * @param mixed $payload
     * @param int|null $httpStatusCode
     * @throws InvalidJsonDataFormatException
     */
    public function __construct(string $callbackName, mixed $payload, ?int $httpStatusCode = null)
    {
        $content = $callbackName . Quote::bracket(SimpleJSON::encode($payload));

        parent::__construct($content, $httpStatusCode);

        $this->withHeader(CommonHttpHeader::CONTENT_TYPE, CommonMimeType::JS);
    }
}
