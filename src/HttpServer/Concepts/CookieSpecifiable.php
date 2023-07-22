<?php

namespace Magpie\HttpServer\Concepts;

use Magpie\HttpServer\Cookies\CookieSameSiteAttribute;
use Magpie\HttpServer\Request;

/**
 * May specify cookie
 */
interface CookieSpecifiable
{
    /**
     * Cookie name
     * @return string
     */
    public function getName() : string;


    /**
     * Specify cookie path
     * @param string $path
     * @return $this
     */
    public function withPath(string $path) : static;


    /**
     * Specify cookie domain
     * @param string $domain
     * @return $this
     */
    public function withDomain(string $domain) : static;


    /**
     * Specify cookie's secure mode (null for auto)
     * @param bool|null $isSecured
     * @return $this
     */
    public function withSecureMode(?bool $isSecured) : static;


    /**
     * Specify if cookie can be accessible through HTTP only (not accessible by scripts, e.g. Javascript)
     * @param bool $isHttpOnly
     * @return $this
     */
    public function withHttpOnly(bool $isHttpOnly = true) : static;


    /**
     * Specify cookie same site attributes
     * @param CookieSameSiteAttribute $attr
     * @return $this
     */
    public function withSameSite(CookieSameSiteAttribute $attr) : static;


    /**
     * Render the specification
     * @param Request|null $request
     * @return void
     * @internal
     */
    public function _render(?Request $request) : void;
}