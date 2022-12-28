<?php

namespace Magpie\Facades\Http\Curl\Impls;

use CurlHandle;
use Magpie\Facades\Http\Curl\CurlClientException;
use Magpie\Facades\Http\Exceptions\ClientException;
use Magpie\General\Traits\StaticClass;

/**
 * Safe-wrapper for CURL's operations
 * @internal
 */
class CurlSafeUtils
{
    use StaticClass;


    /**
     * Normalized `curl_setopt`
     * @param CurlHandle $ch
     * @param int $option
     * @param mixed $value
     * @return void
     * @throws ClientException
     */
    public static function setOpt(CurlHandle $ch, int $option, mixed $value) : void
    {
        $result = curl_setopt($ch, $option, $value);
        static::checkResult($ch, $result);
    }


    /**
     * Check CURL's result
     * @param CurlHandle $ch
     * @param mixed $result
     * @return void
     * @throws ClientException
     */
    public static function checkResult(CurlHandle $ch, mixed $result) : void
    {
        if ($result !== false) return;

        $errNo = curl_errno($ch);
        $errMessage = curl_error($ch);
        throw new CurlClientException($errNo, $errMessage);
    }
}