<?php

namespace Magpie\HttpServer;

use Magpie\HttpServer\Concepts\Renderable;

/**
 * Common rendering support
 */
abstract class CommonRenderable implements Renderable
{
    /**
     * @inheritDoc
     */
    public final function render() : void
    {
        $this->onRender();
    }


    /**
     * Render the response
     * @return void
     */
    protected abstract function onRender() : void;


    /**
     * Send headers in response
     * @param array<string, string> $headerNames Header names
     * @param array<string, string|array> $headerValues Header values
     * @return void
     */
    protected static function sendHeaders(array $headerNames, array $headerValues) : void
    {
        foreach ($headerValues as $headerKey => $headerValue) {
            $headerName = $headerNames[$headerKey] ?? null;
            if ($headerName === null) continue; // Silenced

            if (is_array($headerValue)) {
                $isFirst = true;
                foreach ($headerValue as $subValue) {
                    static::sendHeader($headerName, $subValue, $isFirst);
                    $isFirst = false;
                }
            } else if (is_string($headerValue)) {
                static::sendHeader($headerName, $headerValue);
            }
        }
    }


    /**
     * Send header in response
     * @param string $headerName
     * @param string $value
     * @param bool $isReplace
     * @return void
     */
    protected static function sendHeader(string $headerName, string $value, bool $isReplace = true) : void
    {
        header("$headerName: $value", $isReplace);
    }
}