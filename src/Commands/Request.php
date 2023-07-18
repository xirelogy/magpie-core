<?php

namespace Magpie\Commands;

/**
 * Representation of a console request
 */
abstract class Request
{
    /**
     * @var string Request command
     */
    public readonly string $command;
    /**
     * @var OptionsCollection Options
     */
    public readonly OptionsCollection $options;
    /**
     * @var ArgumentsCollection Arguments
     */
    public readonly ArgumentsCollection $arguments;
    /**
     * @var array<string, string|bool> Options (raw)
     */
    protected readonly array $rawOptions;
    /**
     * @var array<string, string> Arguments (raw)
     */
    protected readonly array $rawArguments;


    /**
     * Constructor
     * @param string $command
     * @param array<string, string|bool> $options
     * @param array<string, string> $arguments
     */
    protected function __construct(string $command, array $options, array $arguments)
    {
        $this->command = $command;
        $this->rawOptions = $options;
        $this->rawArguments = $arguments;

        $this->options = static::createOptionsCollection($options);
        $this->arguments = static::createArgumentsCollection($arguments);
    }


    /**
     * Wrap options into options collection
     * @param array $options
     * @return OptionsCollection
     */
    protected static function createOptionsCollection(array $options) : OptionsCollection
    {
        return new class($options) extends OptionsCollection {

        };
    }


    /**
     * Wrap arguments into arguments collection
     * @param array<string, string> $arguments
     * @return ArgumentsCollection
     */
    protected static function createArgumentsCollection(array $arguments) : ArgumentsCollection
    {
        return new class($arguments) extends ArgumentsCollection {

        };
    }
}