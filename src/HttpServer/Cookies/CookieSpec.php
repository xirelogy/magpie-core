<?php

namespace Magpie\HttpServer\Cookies;

use Magpie\HttpServer\Concepts\CookieSpecifiable;
use Magpie\HttpServer\Request;

/**
 * Cookie specification
 */
abstract class CookieSpec implements CookieSpecifiable
{
    /**
     * @var string Cookie name
     */
    protected readonly string $name;
    /**
     * @var string|null Specific path
     */
    protected ?string $path = null;
    /**
     * @var string|null Specific domain
     */
    protected ?string $domain = null;
    /**
     * @var bool|null If limited to secure connection only
     */
    protected ?bool $isSecured = null;
    /**
     * @var bool If cookie accessible through HTTP only
     */
    protected bool $isHttpOnly = false;
    /**
     * @var CookieSameSiteAttribute|null Same site attribute
     */
    protected ?CookieSameSiteAttribute $sameSiteAttr = null;


    /**
     * Constructor
     * @param string $name
     */
    protected function __construct(string $name)
    {
        $this->name = $name;
    }


    /**
     * @inheritDoc
     */
    public final function getName() : string
    {
        return $this->name;
    }


    /**
     * @inheritDoc
     */
    public final function withPath(string $path) : static
    {
        $this->path = $path;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function withDomain(string $domain) : static
    {
        $this->domain = $domain;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function withSecureMode(?bool $isSecured) : static
    {
        $this->isSecured = $isSecured;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function withHttpOnly(bool $isHttpOnly = true) : static
    {
        $this->isHttpOnly = $isHttpOnly;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public final function withSameSite(CookieSameSiteAttribute $attr) : static
    {
        $this->sameSiteAttr = $attr;
        return $this;
    }


    /**
     * Create cookie options
     * @param Request|null $request
     * @return array
     * @internal
     */
    protected final function _createCookieOptions(?Request $request) : array
    {
        $ret = [];

        if ($this->path !== null) $ret['path'] = $this->path;
        if ($this->domain !== null) $ret['domain'] = $this->domain;

        if ($this->isCookieSecure($request)) $ret['secure'] = true;
        if ($this->isHttpOnly) $ret['httponly'] = true;

        if ($this->sameSiteAttr !== null) $ret['samesite'] = $this->sameSiteAttr->value;

        return $ret;
    }


    /**
     * If the cookie shall be sent securely
     * @param Request|null $request
     * @return bool
     */
    protected final function isCookieSecure(?Request $request) : bool
    {
        if ($this->isSecured !== null) return $this->isSecured;
        if ($request === null) return false;

        return $request->fullUri->scheme === 'https';
    }


    /**
     * @inheritDoc
     * @internal
     */
    public final function _render(?Request $request) : void
    {
        $this->onRender($request);
    }


    /**
     * Render the specification
     * @param Request|null $request
     * @return void
     */
    protected abstract function onRender(?Request $request) : void;
}