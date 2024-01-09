<?php

namespace Magpie\Models\Providers\Sqlite\Exceptions;

use Throwable;

/**
 * Exceptions due to unexpected data (format) during SQLite parsing
 */
class SqliteUnexpectedDataParserException extends SqliteUnexpectedParserException
{
    /**
     * Constructor
     * @param string|null $reason
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(?string $reason = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = static::formatMessage($reason);

        parent::__construct($message, $previous, $code);
    }


    /**
     * Format the message
     * @param string|null $reason
     * @return string
     */
    protected static function formatMessage(?string $reason) : string
    {
        $defaultMessage = _l('Unexpected data format');

        if ($reason === null) return $defaultMessage;

        return _format_l(
            $defaultMessage,
            'Unexpected data format: {{0}}',
            $reason,
        );
    }
}