<?php

namespace Magpie\Facades\Http;

use Exception;
use Magpie\Cryptos\X509\Certificate;
use Magpie\General\Concepts\Packable;
use Magpie\General\Packs\PackContext;
use Magpie\General\Traits\CommonPackable;

/**
 * HTTP client response
 */
abstract class HttpClientResponse implements Packable
{
    use CommonPackable;


    /**
     * The scheme
     * @return string
     */
    public abstract function getScheme() : string;


    /**
     * The HTTP version
     * @return string|null
     */
    public abstract function getHttpVersion() : ?string;


    /**
     * The HTTP status code in response
     * @return int
     */
    public abstract function getHttpStatusCode() : int;


    /**
     * The headers in the response
     * @return HttpClientResponseHeaders
     */
    public abstract function getHeaders() : HttpClientResponseHeaders;


    /**
     * The response body
     * @return HttpClientResponseBody
     */
    public abstract function getBody() : HttpClientResponseBody;


    /**
     * Server certificates (server certificate and certificate chain)
     * @return iterable<Certificate>
     * @throws Exception
     */
    public abstract function getCertificates() : iterable;


    /**
     * Local address
     * @return string|null
     */
    public abstract function getLocalAddress() : ?string;


    /**
     * Remote address
     * @return string|null
     */
    public abstract function getRemoteAddress() : ?string;


    /**
     * Time statistics
     * @return HttpClientRequestTimeStatistics|null
     */
    public abstract function getTimeStatistics() : ?HttpClientRequestTimeStatistics;


    /**
     * @inheritDoc
     */
    protected function onPack(object $ret, PackContext $context) : void
    {
        $ret->httpStatusCode = $this->getHttpStatusCode();
        $ret->headers = $this->getHeaders();
        $ret->body = $this->getBody();
    }
}