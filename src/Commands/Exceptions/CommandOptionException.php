<?php

namespace Magpie\Commands\Exceptions;

use Magpie\Exceptions\SimplifiedCommonException;
use Throwable;

/**
 * Command option related exceptions
 */
abstract class CommandOptionException extends SimplifiedCommonException
{
    /**
     * @var string Option name
     */
    public readonly string $optionName;
    /**
     * @var string Associated command
     */
    public readonly string $command;


    /**
     * Constructor
     * @param string $optionName
     * @param string $command
     * @param string $message
     * @param Throwable|null $previous
     */
    protected function __construct(string $optionName, string $command, string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, $previous);

        $this->optionName = $optionName;
        $this->command = $command;
    }


    /**
     * Format option name
     * @param string $optionName
     * @return string
     */
    protected static function formatOptionName(string $optionName) : string
    {
        return '--' . $optionName;
    }
}