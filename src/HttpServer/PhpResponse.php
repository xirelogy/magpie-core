<?php

namespace Magpie\HttpServer;

use Magpie\General\Traits\StaticClass;
use Magpie\Routes\Concepts\RouteResponseListenable;

/**
 * Expose standard PHP response handlers that supports extensibility
 */
final class PhpResponse
{
    use StaticClass;

    /**
     * @var RouteResponseListenable|null A specific listener to be notified on calls
     */
    protected static ?RouteResponseListenable $listener = null;


    /**
     * Specify a listener to be notified on calls
     * @param RouteResponseListenable|null $listener
     * @return RouteResponseListenable|null The previous listener
     */
    public static function listen(?RouteResponseListenable $listener) : ?RouteResponseListenable
    {
        $previousListener = static::$listener;
        static::$listener = $listener;
        return $previousListener;
    }


    /**
     * Set the HTTP response code
     * @param int $code
     * @return void
     */
    public static function httpResponseCode(int $code) : void
    {
        http_response_code($code);
        static::$listener?->onHttpResponseCode($code);
    }


    /**
     * Set response header
     * @param string $headerLine
     * @param bool $isReplacePrevious
     * @param int $responseCode
     * @return void
     */
    public static function header(string $headerLine, bool $isReplacePrevious = true, int $responseCode = 0) : void
    {
        header($headerLine, $isReplacePrevious, $responseCode);
        static::$listener?->onHeader($headerLine, $isReplacePrevious, $responseCode);
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
        $ret = setrawcookie($name, $value, $options);
        static::$listener?->onSetRawCookie($name, $value, $options, $ret);
        return $ret;
    }
}