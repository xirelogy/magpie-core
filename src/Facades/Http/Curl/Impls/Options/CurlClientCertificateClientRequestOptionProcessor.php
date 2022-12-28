<?php

namespace Magpie\Facades\Http\Curl\Impls\Options;

use Magpie\Exceptions\UnexpectedException;
use Magpie\Facades\Http\Curl\CurlHttpClient;
use Magpie\Facades\Http\Curl\Supports\CurlHttpClientRequestOptionContext;
use Magpie\Facades\Http\Options\ClientCertificateClientRequestOption;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionContext;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionProcessor;
use Magpie\General\Factories\Annotations\FeatureMatrixTypeClass;

/**
 * ClientCertificateClientRequestOption processor for CURL
 * @internal
 */
#[FeatureMatrixTypeClass(ClientCertificateClientRequestOption::TYPECLASS, CurlHttpClient::TYPECLASS, HttpClientRequestOptionProcessor::class)]
class CurlClientCertificateClientRequestOptionProcessor extends HttpClientRequestOptionProcessor
{
    /**
     * @inheritDoc
     */
    public function apply(HttpClientRequestOptionContext $context) : void
    {
        if (!$context instanceof CurlHttpClientRequestOptionContext) throw new UnexpectedException();

        $map = function() use($context) : iterable {
            if (!$this->option instanceof ClientCertificateClientRequestOption) throw new UnexpectedException();

            yield from $context->translateCryptoContentOptions($this->option->certificate,
                CURLOPT_SSLCERT,
                CURLOPT_SSLCERTTYPE,
                CURLOPT_SSLCERTPASSWD,
            );
            yield from $context->translateCryptoContentOptions($this->option->privateKey,
                CURLOPT_SSLKEY,
                CURLOPT_SSLKEYTYPE,
                CURLOPT_SSLKEYPASSWD,
            );
        };
        foreach ($map as $optionKey => $optionValue) {
            $context->setOpt($optionKey, $optionValue);
        }
    }
}