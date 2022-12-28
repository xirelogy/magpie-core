<?php

namespace Magpie\Facades\Http\Curl;

use Magpie\Cryptos\Context;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Http\HttpClient;
use Magpie\Facades\Http\HttpClientPendingRequest;
use Magpie\Facades\Http\HttpClientRequestOption;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\Logs\Concepts\Loggable;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * HTTP client utilizing CURL
 */
#[FactoryTypeClass(CurlHttpClient::TYPECLASS, HttpClient::class)]
class CurlHttpClient extends HttpClient
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'curl';

    /**
     * @var Loggable|null Logger interface
     */
    protected ?Loggable $logger = null;


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function setLogger(Loggable $logger) : bool
    {
        $this->logger = $logger;
        return true;
    }


    /**
     * @inheritDoc
     */
    public function prepare(string $method, string $url) : HttpClientPendingRequest
    {
        return new class($method, $url, $this->headers, $this->options, $this->logger) extends CurlHttpClientPendingRequest {
            /**
             * Constructor
             * @param string $method
             * @param string $url
             * @param array<string, mixed> $parentHeaders
             * @param array<HttpClientRequestOption> $parentOptions
             * @param Loggable|null $logger
             * @throws SafetyCommonException
             */
            public function __construct(string $method, string $url, array $parentHeaders, array $parentOptions, ?Loggable $logger)
            {
                parent::__construct($method, $url, $parentHeaders, $parentOptions, $logger);
            }
        };
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitialize() : static
    {
        return new static();
    }


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        $registrar
            ->depends(Context::class)
            ->provides(HttpClient::class)
            ;

        return true;
    }


    /**
     * @inheritDoc
     */
    public static function systemBoot(BootContext $context) : void
    {
        ClassFactory::includeDirectory(__DIR__);
        ClassFactory::includeDirectory(__DIR__ . '/Impls/Options');

        ClassFactory::setDefaultTypeClassCheck(HttpClient::class, function () : ?string {
            if (!extension_loaded('curl')) return null;
            return static::TYPECLASS;
        });
    }
}