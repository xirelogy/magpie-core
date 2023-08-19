<?php

namespace Magpie\Routes\Concepts;

/**
 * May listen to calls that alter the web response sent to PHP system
 */
interface RouteResponseListenable
{
    /**
     * A call to set the HTTP response code
     * @param int $code
     * @return void
     */
    public function onHttpResponseCode(int $code) : void;


    /**
     * A call to add response header
     * @param string $headerLine
     * @param bool $isReplacePrevious
     * @param int $responseCode Specific response code to be set, if not 0
     * @return void
     */
    public function onHeader(string $headerLine, bool $isReplacePrevious, int $responseCode) : void;


    /**
     * A call to add response cookie (raw)
     * @param string $name
     * @param string $value
     * @param array $options
     * @param bool $result
     * @return void
     */
    public function onSetRawCookie(string $name, string $value, array $options, bool $result) : void;
}