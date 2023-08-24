<?php

namespace Magpie\Routes;

use Carbon\Carbon;
use Magpie\Codecs\Formats\HttpDateTimeFormatter;
use Magpie\General\Contexts\ClosureScoped;
use Magpie\General\Contexts\Scoped;
use Magpie\General\Str;
use Magpie\HttpServer\PhpResponse;
use Magpie\Routes\Concepts\RouteResponseListenable;

/**
 * Common implementation of RouteResponseListenable
 */
class CommonRouteResponseListener implements RouteResponseListenable
{
    /**
     * @var RouteResponseListenable|null The previous listener
     */
    private ?RouteResponseListenable $previous;


    /**
     * Constructor
     * @param RouteResponseListenable|null $previous
     */
    public function __construct(?RouteResponseListenable $previous = null)
    {
        $this->previous = $previous;
    }


    /**
     * Create a listening scope
     * @return Scoped
     */
    public function createListeningScoped() : Scoped
    {
        return ClosureScoped::create(function () {
            $this->previous = PhpResponse::listen($this);
        }, function () {
            PhpResponse::listen($this->previous);
            $this->previous = null;
        });
    }


    /**
     * @inheritDoc
     */
    public final function onHttpResponseCode(int $code) : void
    {
        $this->onSpecificHttpResponseCode($code);

        $this->previous?->onHttpResponseCode($code);
    }


    /**
     * Handle call to set the HTTP response code
     * @param int $code
     * @return void
     */
    protected function onSpecificHttpResponseCode(int $code) : void
    {

    }


    /**
     * @inheritDoc
     */
    public final function onHeader(string $headerLine, bool $isReplacePrevious, int $responseCode) : void
    {
        $this->onSpecificHeader($headerLine, $isReplacePrevious);
        if ($responseCode !== 0) $this->onSpecificHttpResponseCode($responseCode);

        $this->previous?->onHeader($headerLine, $isReplacePrevious, $responseCode);
    }


    /**
     * Handle call to add response header
     * @param string $headerLine
     * @param bool $isReplacePrevious
     * @return void
     */
    protected function onSpecificHeader(string $headerLine, bool $isReplacePrevious) : void
    {

    }


    /**
     * @inheritDoc
     */
    public final function onSetRawCookie(string $name, string $value, array $options, bool $result) : void
    {
        if ($result) {
            $this->onSpecificHeader(static::composeCookieHeader($name, $value, $options), false);
        }

        $this->previous?->onSetRawCookie($name, $value, $options, $result);
    }


    /**
     * Compose the cookie header
     * @param string $name
     * @param string $value
     * @param array $options
     * @return string
     */
    protected static function composeCookieHeader(string $name, string $value, array $options) : string
    {
        $ret = 'Set-Cookie: ';

        $components = [];
        foreach (static::getCookieComponents($name, $value, $options) as $cookieKey => $cookieValue) {
            if (!Str::isNullOrEmpty($cookieValue)) {
                $components[] = "$cookieKey=$cookieValue";
            } else {
                $components[] = $cookieKey;
            }
        }
        $ret .= implode('; ', $components);

        return $ret;
    }


    /**
     * Get cookie components
     * @param string $name
     * @param string $value
     * @param array $options
     * @return iterable<string, string>
     */
    protected static function getCookieComponents(string $name, string $value, array $options) : iterable
    {
        yield $name => $value;

        if (array_key_exists('expires', $options)) {
            $expires = $options['expires'];
            $expiryTime = Carbon::createFromTimestamp($expires);
            yield 'expires' => HttpDateTimeFormatter::create()->format($expiryTime);

            $maxAge = $expires - time();
            if ($maxAge < 0) $maxAge = 0;
            yield 'Max-Age' => $maxAge;
        }

        if (array_key_exists('path', $options)) {
            yield 'path' => $options['path'];
        }

        if (array_key_exists('domain', $options)) {
            yield 'domain' => $options['domain'];
        }

        if (array_key_exists('secure', $options) && $options['secure'] === true) {
            yield 'secure' => '';
        }

        if (array_key_exists('httponly', $options) && $options['httponly'] === true) {
            yield 'HttpOnly' => '';
        }

        if (array_key_exists('samesite', $options)) {
            yield 'SameSite' => $options['samesite'];
        }
    }
}