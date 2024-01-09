<?php

namespace Magpie\Models\Providers\Sqlite\Exceptions;

use Throwable;

/**
 * Exceptions due to token stream is full (cannot rewind)
 */
class SqliteTokenStreamFullException extends SqliteParserException
{
    /**
     * Constructor
     * @param string|null $message
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(?string $message = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = $message ?? _l('Token stream is full');

        parent::__construct($message, $previous, $code);
    }
}
