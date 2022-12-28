<?php

namespace Magpie\Commands\Exceptions;

use Magpie\Exceptions\ArgumentException;
use Throwable;

/**
 * Exception due to missing argument in command
 */
class MissingCommandArgumentException extends ArgumentException
{
    /**
     * @var string Associated command
     */
    public readonly string $command;


    /**
     * Constructor
     * @param string $argName
     * @param string $command
     * @param Throwable|null $previous
     */
    public function __construct(string $argName, string $command, ?Throwable $previous = null)
    {
        $message = static::formatMessage($argName, $command);

        parent::__construct($argName, $message, $previous);

        $this->command = $command;
    }


    /**
     * Format message
     * @param string $argName
     * @param string $command
     * @return string
     */
    protected static function formatMessage(string $argName, string $command) : string
    {
        return _format_safe(_l('Missing argument \'{{0}}\' in command: {{1}}'), $argName, $command)
            ?? _l('Missing argument in command');
    }
}