<?php

namespace Magpie\Commands\Exceptions;

use Throwable;

/**
 * Exception due to missing command option payload
 */
class MissingCommandOptionPayloadException extends CommandOptionException
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
        return _format_safe(_l('Missing payload for command option \'{{0}}\' in command: {{1}}'), static::formatOptionName($optionName), $command)
            ?? _l('Missing payload for command option in command');
    }
}