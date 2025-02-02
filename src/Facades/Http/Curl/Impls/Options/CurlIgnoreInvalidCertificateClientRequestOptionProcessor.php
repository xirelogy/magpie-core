<?php

namespace Magpie\Facades\Http\Curl\Impls\Options;

use Magpie\Exceptions\UnexpectedException;
use Magpie\Facades\Http\Curl\CurlHttpClient;
use Magpie\Facades\Http\Curl\Supports\CurlHttpClientRequestOptionContext;
use Magpie\Facades\Http\Options\IgnoreInvalidCertificateClientRequestOption;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionContext;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionProcessor;
use Magpie\General\Factories\Annotations\FeatureMatrixTypeClass;

/**
 * IgnoreInvalidCertificateClientRequestOption processor for CURL
 * @internal
 */
#[FeatureMatrixTypeClass(IgnoreInvalidCertificateClientRequestOption::TYPECLASS, CurlHttpClient::TYPECLASS, HttpClientRequestOptionProcessor::class)]
class CurlIgnoreInvalidCertificateClientRequestOptionProcessor extends HttpClientRequestOptionProcessor
{
    /**
     * @inheritDoc
     */
    public function apply(HttpClientRequestOptionContext $context) : void
    {
        if (!$context instanceof CurlHttpClientRequestOptionContext) throw new UnexpectedException();
        if (!$this->option instanceof IgnoreInvalidCertificateClientRequestOption) throw new UnexpectedException();

        $context->setOpt(CURLOPT_SSL_VERIFYHOST, 0);
        $context->setOpt(CURLOPT_SSL_VERIFYPEER, false);
    }
}