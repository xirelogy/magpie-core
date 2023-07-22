<?php

namespace Magpie\HttpServer\Cookies;

use Carbon\CarbonInterface;
use Magpie\General\DateTimes\Duration;
use Magpie\HttpServer\Request;

/**
 * Set a cookie
 */
class SetCookie extends CookieSpec
{
    /**
     * @var string Associated value
     */
    protected string $value;
    /**
     * @var CarbonInterface|Duration|null Specific expiry
     */
    protected CarbonInterface|Duration|null $expiry = null;


    /**
     * Constructor
     * @param string $name
     * @param string $value
     */
    protected function __construct(string $name, string $value)
    {
        parent::__construct($name);

        $this->value = $value;
    }


    /**
     * Specify cookie value
     * @param string $value
     * @return $this
     */
    public function withValue(string $value) : static
    {
        $this->value = $value;
        return $this;
    }


    /**
     * Specify expiry
     * @param CarbonInterface|Duration|int $expiry
     * @return $this
     */
    public function withExpiry(CarbonInterface|Duration|int $expiry) : static
    {
        if (is_int($expiry)) $expiry = Duration::accept($expiry);
        $this->expiry = $expiry;
        return $this;
    }


    /**
     * @inheritDoc
     */
    protected function onRender(?Request $request) : void
    {
        $options = $this->_createCookieOptions($request);

        $expires = $this->getExpiresForOptions();
        if ($expires !== null) $options['expires'] = $expires;

        setrawcookie($this->name, rawurlencode($this->value), $options);
    }


    /**
     * Translate expiry for options
     * @return int|null
     */
    protected function getExpiresForOptions() : ?int
    {
        if ($this->expiry instanceof CarbonInterface) return $this->expiry->getTimestamp();
        if ($this->expiry instanceof Duration) return time() + $this->expiry->getSeconds();
        return null;
    }


    /**
     * Create an instance
     * @param string $name
     * @param string $value
     * @return static
     */
    public static function for(string $name, string $value) : static
    {
        return new static($name, $value);
    }
}