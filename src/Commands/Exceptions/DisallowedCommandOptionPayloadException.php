<?php

namespace Magpie\Commands\Exceptions;

use Throwable;

/**
 * Exception due to command option payload provided when it is disallowed
 */
class DisallowedCommandOptionPayloadException extends CommandOptionException
{
    /**
     * Constructor
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
        return _format_safe(_l('Command option \'{{0}}\' does not allow payload in command: {{1}}'), static::formatOptionName($optionName), $command)
            ?? _l('Command option does not allow payload in command');
    }
}