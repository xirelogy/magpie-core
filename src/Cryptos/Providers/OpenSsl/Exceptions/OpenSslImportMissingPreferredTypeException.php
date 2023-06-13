<?php

namespace Magpie\Cryptos\Providers\OpenSsl\Exceptions;

use Throwable;

/**
 * Exception during OpenSSL import - missing preferred type
 */
class OpenSslImportMissingPreferredTypeException extends OpenSslImportException
{
    public function __construct(?string $message = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = $message ?? _l('Missing preferred content type during import');

        parent::__construct($message, $previous, $code);
    }
}