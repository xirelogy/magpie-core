<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Exceptions;

use Throwable;

/**
 * Detailed OpenSSL related errors
 */
class DetailedOpenSslException extends OpenSslException
{
    /**
     * @var array<string> Error details
     */
    public readonly array $details;


    /**
     * Constructor
     * @param array<string> $details
     * @param Throwable|null $previous
     */
    public function __construct(array $details, ?Throwable $previous = null)
    {
        $message = $details[count($details) - 1] ?? '?';

        parent::__construct($message, $previous);

        $this->details = $details;
    }
}