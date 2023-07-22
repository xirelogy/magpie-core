<?php

namespace Magpie\HttpServer;

use Magpie\General\Names\CommonHttpStatusCode;
use Magpie\HttpServer\Concepts\CookieSpecifiable;
use Magpie\HttpServer\Concepts\WithContentSpecifiable;
use Magpie\HttpServer\Concepts\WithCookieSpecifiable;
use Magpie\HttpServer\Concepts\WithHeaderSpecifiable;
use Magpie\HttpServer\Concepts\WithHttpStatusCodeSpecifiable;
use Magpie\HttpServer\Traits\CommonCookieSpecifiable;
use Magpie\HttpServer\Traits\CommonHeaderSpecifiable;
use Magpie\Objects\Uri;

/**
 * Representation of a response
 */
class Response extends CommonRenderable implements WithHttpStatusCodeSpecifiable, WithHeaderSpecifiable, WithCookieSpecifiable, WithContentSpecifiable
{
    use CommonHeaderSpecifiable;
    use CommonCookieSpecifiable;


    /**
     * @var int|null HTTP status code
     */
    protected ?int $httpStatusCode = null;
    /**
     * @var string Response content
     */
    public string $content;
    /**
     * @var array<string, string> Header names
     */
    protected array $headerNames = [];
    /**
     * @var array<string, string|array> Headers values
     */
    protected array $headerValues = [];
    /**
     * @var array<CookieSpecifiable> Cookies
     */
    protected array $cookies = [];


    /**
     * Constructor
     * @param string $content
     * @param int|null $httpStatusCode
     */
    public function __construct(string $content = '', ?int $httpStatusCode = null)
    {
        $this->httpStatusCode = $httpStatusCode;
        $this->content = $content;
    }


    /**
     * @inheritDoc
     */
    public function withStatusCode(int $httpStatusCode) : static
    {
        $this->httpStatusCode = $httpStatusCode;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withContent(string $content) : static
    {
        $this->content = $content;
        return $this;
    }


    /**
     * Shorthand to redirect to a target
     * @param Uri|string $target
     * @param int $httpStatusCode
     * @return $this
     */
    public function redirect(Uri|string $target, int $httpStatusCode = CommonHttpStatusCode::FOUND) : static
    {
        return $this
            ->withStatusCode($httpStatusCode)
            ->withHeader('Location', $target)
            ;
    }


    /**
     * @inheritDoc
     */
    protected function onRender(?Request $request) : void
    {
        if ($this->httpStatusCode !== null) http_response_code($this->httpStatusCode);

        static::sendHeaders($this->headerNames, $this->headerValues);
        static::sendCookies($this->cookies, $request);

        echo $this->content;
    }
}