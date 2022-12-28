<?php

namespace Magpie\Facades\Http\Curl\Impls\Options;

use Magpie\Exceptions\UnexpectedException;
use Magpie\Facades\Http\Curl\CurlHttpClient;
use Magpie\Facades\Http\Curl\Supports\CurlHttpClientRequestOptionContext;
use Magpie\Facades\Http\Options\TimeoutClientRequestOption;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionContext;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionProcessor;
use Magpie\General\Factories\Annotations\FeatureMatrixTypeClass;
use Magpie\General\MultiPrecision;

/**
 * TimeoutClientRequestOption processor for CURL
 * @internal
 */
#[FeatureMatrixTypeClass(TimeoutClientRequestOption::TYPECLASS, CurlHttpClient::TYPECLASS, HttpClientRequestOptionProcessor::class)]
class CurlTimeoutClientRequestOptionProcessor extends HttpClientRequestOptionProcessor
{
    /**
     * @inheritDoc
     */
    public function apply(HttpClientRequestOptionContext $context) : void
    {
        if (!$context instanceof CurlHttpClientRequestOptionContext) throw new UnexpectedException();
        if (!$this->option instanceof TimeoutClientRequestOption) throw new UnexpectedException();

        $matchedValue = MultiPrecision::matchPrecision($this->option->timeout, [-3, 0], $matchedPrecision);
        switch ($matchedPrecision) {
            case -3:
                $context->setOpt(CURLOPT_TIMEOUT_MS, $matchedValue);
                break;
            case 0:
                $context->setOpt(CURLOPT_TIMEOUT, $matchedValue);
                break;
        }
    }
}