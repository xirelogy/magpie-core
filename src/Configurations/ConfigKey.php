<?php

namespace Magpie\Configurations;

use Magpie\Codecs\Parsers\Parser;

/**
 * Configuration key
 * @template T
 */
class ConfigKey
{
    /**
     * @var ConfigName Key name
     */
    public readonly ConfigName $name;
    /**
     * @var bool If required
     */
    public readonly bool $isRequired;
    /**
     * @var Parser<T>|null Parser to be used for processing the value
     */
    public ?Parser $parser;
    /**
     * @var T|null Default value for optional keys when not available
     */
    public mixed $defaultValue;
    /**
     * @var string|null Specific description
     */
    public ?string $desc;


    /**
     * Constructor
     * @param ConfigName $name
     * @param bool $isRequired
     * @param Parser<T>|null $parser
     * @param T|null $defaultValue
     * @param string|null $desc
     */
    protected function __construct(ConfigName $name, bool $isRequired, ?Parser $parser, mixed $defaultValue, ?string $desc)
    {
        $this->name = $name;
        $this->isRequired = $isRequired;
        $this->defaultValue = $defaultValue;
        $this->parser = $parser;
        $this->desc = $desc;
    }


    /**
     * Create an instance
     * @param ConfigName|string $name
     * @param bool $isRequired
     * @param Parser<T>|null $parser
     * @param T|null $defaultValue
     * @param string|null $desc
     * @return static
     */
    public static function create(ConfigName|string $name, bool $isRequired, ?Parser $parser = null, mixed $defaultValue = null, ?string $desc = null) : static
    {
        if (is_string($name)) {
            $name = ConfigName::parse($name);
        }

        return new static($name, $isRequired, $parser, $defaultValue, $desc);
    }
}