<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Exceptions;

use Throwable;

/**
 * A specific OpenSSL exception
 */
class SpecificOpenSslException extends OpenSslException
{
    /**
     * @var string|null Source causing the specific exception
     */
    public readonly ?string $source;


    /**
     * Constructor
     * @param string $message
     * @param string|null $source
     * @param Throwable|null $previous
     */
    public function __construct(string $message, ?string $source = null, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);

        $this->source = $source;
    }
}