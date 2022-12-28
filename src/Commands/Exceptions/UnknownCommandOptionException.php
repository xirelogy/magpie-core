<?php

namespace Magpie\Commands\Exceptions;

use Throwable;

/**
 * Exception due to unknown command option
 */
class UnknownCommandOptionException extends CommandOptionException
{
    /**
     * @param string $optionName
     * @param string $command
     * @param Throwable|null $previous
     */
    public function __construct(string $optionName, string $command, ?Throwable $previous = null)
    {
        $message = static::formatMessage($optionName, $command);

        parent::__construct($optionName, $command, $message, $previous);
    }


    /**
     * Format message
     * @param string $optionName
     * @param string $command
     * @return string
     */
    protected static function formatMessage(string $optionName, string $command) : string
    {
        return _format_safe(_l('Unknown command option \'{{0}}\' for command: {{1}}'), static::formatOptionName($optionName), $command)
            ?? _l('Unknown command option for command');
    }
}