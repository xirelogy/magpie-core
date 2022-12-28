<?php

namespace Magpie\HttpServer;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Names\CommonMimeType;
use Magpie\General\Simples\SimpleXML;

/**
 * Representation of an XML response
 */
class XmlResponse extends Response
{
    /**
     * Constructor
     * @param mixed $payload
     * @param int|null $httpStatusCode
     * @param string $rootElementName
     * @throws SafetyCommonException
     */
    public function __construct(mixed $payload, ?int $httpStatusCode = null, string $rootElementName = SimpleXML::DEFAULT_ROOT_NAME)
    {
        $content = SimpleXML::encode($payload, $rootElementName);

        parent::__construct($content, $httpStatusCode);

        $this->withHeader(CommonHttpHeader::CONTENT_TYPE, CommonMimeType::XML);
    }
}