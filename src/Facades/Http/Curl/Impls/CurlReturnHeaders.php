<?php

namespace Magpie\Facades\Http\Curl\Impls;

use CurlHandle;
use Magpie\Facades\Http\Exceptions\ClientException;
use Magpie\Facades\Http\HttpClientResponseHeaders;

/**
 * Returned headers from CURL
 * @internal
 */
class CurlReturnHeaders extends HttpClientResponseHeaders
{
    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct();
    }


    /**
     * Add a header into collection
     * @param string $name
     * @param string $value
     * @param bool $isAllowDuplicates
     * @return void
     */
    public function add(string $name, string $value, bool $isAllowDuplicates = false) : void
    {
        $this->addHeader($name, $value, $isAllowDuplicates);
    }


    /**
     * Capture from CURL handle
     * @param CurlHandle $ch
     * @return static
     * @throws ClientException
     */
    public static function from(CurlHandle $ch) : static
    {
        $ret = new static();

        CurlSafeUtils::setOpt($ch, CURLOPT_HEADERFUNCTION, function (CurlHandle $ch, string $header) use ($ret) : int {
            // Try to decode header text
            $headerLength = strlen($header);
            $headerElements = explode(':', $header, 2);
            if (count($headerElements) < 2) return $headerLength;

            // Set header name/value
            $ret->add(trim($headerElements[0]), trim($headerElements[1]), true);
            return $headerLength;
        });

        return $ret;
    }
}