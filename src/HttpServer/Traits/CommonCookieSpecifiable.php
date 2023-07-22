<?php

namespace Magpie\HttpServer\Traits;

use Magpie\HttpServer\Concepts\CookieSpecifiable;

/**
 * Common implementation of WithCookieSpecifiable
 */
trait CommonCookieSpecifiable
{
    /**
     * @inheritDoc
     */
    public function withCookie(CookieSpecifiable $cookie) : static
    {
        $this->cookies[] = $cookie;
        return $this;
    }
}