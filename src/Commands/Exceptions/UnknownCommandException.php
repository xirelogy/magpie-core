<?php

namespace Magpie\Commands\Exceptions;

use Magpie\Exceptions\SimplifiedCommonException;
use Throwable;

/**
 * Exception due to unknown command
 */
class UnknownCommandException extends SimplifiedCommonException
{
    /**
     * Constructor
     * @param string $command
     * @param Throwable|null $previous
     */
    public function __construct(string $command, ?Throwable $previous = null)
    {
        $message = static::formatMessage($command);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $command
     * @return string
     */
    protected static function formatMessage(string $command) : string
    {
        return _format_safe(_l('Unknown command: \'{{0}}\''), $command)
            ?? _l('Unknown command');
    }
}