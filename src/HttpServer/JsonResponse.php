<?php

namespace Magpie\HttpServer;

use Magpie\Exceptions\InvalidJsonDataFormatException;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonMimeType;
use Magpie\General\Simples\SimpleJSON;

/**
 * Representation of a JSON response
 */
class JsonResponse extends Response
{
    /**
     * Constructor
     * @param mixed $payload
     * @param int|null $httpStatusCode
     * @throws InvalidJsonDataFormatException
     */
    public function __construct(mixed $payload, ?int $httpStatusCode = null)
    {
        $content = SimpleJSON::encode($payload);

        parent::__construct($content, $httpStatusCode);

        $this->withHeader(CommonHttpHeader::CONTENT_TYPE, CommonMimeType::JSON);
    }
}