<?php

namespace Magpie\HttpServer\Concepts;

/**
 * Cookie in response may be specified
 */
interface WithCookieSpecifiable
{
    /**
     * Set cookie
     * @param CookieSpecifiable $cookie
     * @return $this
     */
    public function withCookie(CookieSpecifiable $cookie) : static;
}