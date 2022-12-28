<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to duplicated key
 */
class DuplicatedKeyException extends DuplicatedException
{
    /**
     * Constructor
     * @param string $key
     * @param string|null $purpose
     * @param Throwable|null $previous
     */
    public function __construct(string $key, ?string $purpose = null, ?Throwable $previous = null)
    {
        $message = static::formatMessage($key, $purpose);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $key
     * @param string|null $purpose
     * @return string
     */
    protected static function formatMessage(string $key, ?string $purpose) : string
    {
        if ($purpose !== null) {
            return _format_safe(_l('Duplicated {{1}} key: {{0}}'), $key, $purpose) ?? _l('Duplicated key');
        } else {
            return _format_safe(_l('Duplicated key: {{0}}'), $key) ?? _l('Duplicated key');
        }
    }
}