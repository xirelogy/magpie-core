<?php

namespace Magpie\Facades\Http\Traits;

use Exception;
use Magpie\Facades\Http\HttpAuthentication;
use Magpie\Facades\Http\Options\ProxyClientRequestOption;
use Magpie\Facades\Http\Options\TimeoutClientRequestOption;
use Magpie\General\DateTimes\Duration;

/**
 * Common options for pending request
 * @requires \Magpie\Facades\Http\HttpClientPendingRequest
 */
trait CommonPendingRequestOptions
{
    /**
     * Specify request timeout options
     * @param int|Duration $timeout
     * @return $this
     * @throws Exception
     */
    public function withTimeoutOption(int|Duration $timeout) : static
    {
        return $this->withOption(TimeoutClientRequestOption::create($timeout));
    }


    /**
     * Specify proxy options
     * @param string $remote
     * @param HttpAuthentication|null $auth
     * @param int $options
     * @return $this
     * @throws Exception
     */
    public function withProxy(string $remote, ?HttpAuthentication $auth = null, int $options = 0) : static
    {
        return $this->withOption(ProxyClientRequestOption::create($remote, $auth, $options));
    }
}