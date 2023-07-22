<?php

namespace Magpie\HttpServer\Cookies;

/**
 * SameSite attribute for cookies
 */
enum CookieSameSiteAttribute : string
{
    /**
     * Strict: cookies can be only sent in a first-party context
     */
    case STRICT = 'Strict';
    /**
     * Lax: allow cookies to be sent also with top-level navigations
     */
    case LAX = 'Lax';
    /**
     * None: no restriction, allow sending cookie in 3rd party context
     */
    case NONE = 'None';
}