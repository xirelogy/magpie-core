<?php

namespace Magpie\Models\Providers\Sqlite\Exceptions;

use Throwable;

/**
 * Exceptions due to invalid lexical state during SQLite parsing
 */
class SqliteInvalidLexStateParserException extends SqliteParserException
{
    /**
     * @var int The invalid state
     */
    public readonly int $state;


    /**
     * Constructor
     * @param int $state
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(int $state, ?Throwable $previous = null, int $code = 0)
    {
        $message = static::formatMessage($state);

        parent::__construct($message, $previous, $code);
    }


    /**
     * Format the message
     * @param int $state
     * @return string
     */
    protected static function formatMessage(int $state) : string
    {
        return _format_l(
            'Invalid lex state',
            'Invalid lex state: \'{{0}}\'',
            $state,
        );
    }

}