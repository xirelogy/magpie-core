<?php

namespace Magpie\Routes;

use Carbon\Carbon;
use Magpie\Codecs\Formats\CookieDateTimeFormatter;
use Magpie\General\Contexts\ClosureScoped;
use Magpie\General\Contexts\Scoped;
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
            $components[] = "$cookieKey=$cookieValue";
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
            yield 'expires' => CookieDateTimeFormatter::create()->format($expiryTime);

            $maxAge = $expires - time();
            if ($maxAge < 0) $maxAge = 0;
            yield 'Max-Age' => $maxAge;
        }
    }
}