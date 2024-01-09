<?php

namespace Magpie\Models\Providers\Sqlite\Exceptions;

use Magpie\Models\Providers\Sqlite\Impls\Concepts\TokenParseable;
use Throwable;

/**
 * Exceptions due to failure during SQLite parsing for specific type
 */
class SqliteCannotParseTypeParserException extends SqliteParserException
{
    /**
     * @var class-string<TokenParseable> The class name that failed to be parsed
     */
    public readonly string $className;


    /**
     * Constructor
     * @param string $className
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(string $className, ?Throwable $previous = null, int $code = 0)
    {
        $message = static::formatMessage($className);

        parent::__construct($message, $previous, $code);
    }


    /**
     * Format the message
     * @param string $className
     * @return string
     */
    protected static function formatMessage(string $className) : string
    {
        return _format_l(
            'Cannot parse given type',
            'Cannot parse expected \'{{0}}\' type',
            $className,
        );
    }
}