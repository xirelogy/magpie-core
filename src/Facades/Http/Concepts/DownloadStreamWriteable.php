<?php

namespace Magpie\Facades\Http\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Http\HttpClientResponseBody;
use Magpie\General\Concepts\StreamWriteable;

/**
 * Writable stream for download
 */
interface DownloadStreamWriteable extends StreamWriteable
{
    /**
     * Finalize the download stream
     * @return HttpClientResponseBody
     * @throws SafetyCommonException
     */
    public function finalizeAsBody() : HttpClientResponseBody;
}