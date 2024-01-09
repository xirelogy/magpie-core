<?php

namespace Magpie\Models\Providers\Sqlite\Exceptions;

use Throwable;

/**
 * Exceptions due to failure caused by a specific reason
 */
class SqliteFailedParserException extends SqliteParserException
{
    public function __construct(string $reason, ?Throwable $previous = null, int $code = 0)
    {
        $message = static::formatMessage($reason);

        parent::__construct($message, $previous, $code);
    }


    /**
     * Format the message
     * @param string $reason
     * @return string
     */
    protected static function formatMessage(string $reason) : string
    {
        return _format_l(
            'Failed while parsing',
            'Failed while parsing: {{0}}',
            $reason,
        );
    }
}