<?php

namespace Magpie\HttpServer;

use Magpie\General\Traits\StaticClass;

/**
 * Expose standard PHP response handlers that supports extensibility
 */
final class PhpResponse
{
    use StaticClass;


    /**
     * Set the HTTP response code
     * @param int $code
     * @return void
     */
    public static function httpResponseCode(int $code) : void
    {
        http_response_code($code);
    }


    /**
     * Set response header
     * @param string $header
     * @param bool $replace
     * @param int $responseCode
     * @return void
     */
    public static function header(string $header, bool $replace = true, int $responseCode = 0) : void
    {
        header($header, $replace, $responseCode);
    }


    /**
     * Set response cookie (raw)
     * @param string $name
     * @param string $value
     * @param array $options
     * @return bool
     */
    public static function setRawCookie(string $name, string $value = '', array $options = []) : bool
    {
        return setrawcookie($name, $value, $options);
    }
}