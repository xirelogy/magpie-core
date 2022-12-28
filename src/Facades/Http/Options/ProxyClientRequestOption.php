<?php

namespace Magpie\Facades\Http\Options;

use Magpie\Facades\Http\HttpAuthentication;
use Magpie\Facades\Http\HttpClientRequestOption;

/**
 * Proxy related options
 */
class ProxyClientRequestOption extends HttpClientRequestOption
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'proxy';
    /**
     * If DNS is forwarded via proxy
     */
    public const OPT_FORWARD_DNS = 2;
    /**
     * If HTTP proxy may be downgraded
     */
    public const OPT_DOWNGRADE_HTTP = 512;

    /**
     * @var string Remote address to proxy to
     */
    public string $remote;
    /**
     * @var HttpAuthentication|null Authentication credentials to the proxy
     */
    public ?HttpAuthentication $auth;
    /**
     * @var int Proxy options
     */
    public int $options;


    /**
     * Constructor
     * @param string $remote
     * @param HttpAuthentication|null $auth
     * @param int $options
     */
    protected function __construct(string $remote, ?HttpAuthentication $auth, int $options)
    {
        parent::__construct();

        $this->remote = $remote;
        $this->auth = $auth;
        $this->options = $options;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * Create a proxy option
     * @param string $remote
     * @param HttpAuthentication|null $auth
     * @param int $options
     * @return static
     */
    public static function create(string $remote, ?HttpAuthentication $auth = null, int $options = 0) : static
    {
        return new static($remote, $auth, $options);
    }
}