<?php

namespace Magpie\Facades\Http\Curl\Impls\Options;

use Magpie\Exceptions\UnexpectedException;
use Magpie\Facades\Http\Curl\CurlHttpClient;
use Magpie\Facades\Http\Curl\Impls\CurlDownloadStreamSetup;
use Magpie\Facades\Http\Curl\Supports\CurlHttpClientRequestOptionContext;
use Magpie\Facades\Http\Options\DownloadSetupClientRequestOption;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionContext;
use Magpie\Facades\Http\Supports\HttpClientRequestOptionProcessor;
use Magpie\General\Factories\Annotations\FeatureMatrixTypeClass;

/**
 * DownloadSetupClientRequestOption processor for CURL
 * @internal
 */
#[FeatureMatrixTypeClass(DownloadSetupClientRequestOption::TYPECLASS, CurlHttpClient::TYPECLASS, HttpClientRequestOptionProcessor::class)]
class CurlDownloadSetupClientRequestOptionProcessor extends HttpClientRequestOptionProcessor
{
    /**
     * @inheritDoc
     */
    public function apply(HttpClientRequestOptionContext $context) : void
    {
        if (!$context instanceof CurlHttpClientRequestOptionContext) throw new UnexpectedException();
        if (!$this->option instanceof DownloadSetupClientRequestOption) throw new UnexpectedException();

        $context->setDownloadSetup(new CurlDownloadStreamSetup($context, $this->option));
    }
}