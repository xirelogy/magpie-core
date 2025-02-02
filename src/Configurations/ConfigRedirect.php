<?php

namespace Magpie\Configurations;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Exceptions\ParseFailedException;

/**
 * Setup for configuration redirection
 * @template T
 */
class ConfigRedirect
{
    /**
     * @var Parser<T>|null Parser instance
     */
    public readonly ?Parser $parser;
    /**
     * @var mixed Default value when value is not available
     */
    public readonly mixed $defaultValue;


    /**
     * Constructor
     * @param Parser|null $parser
     * @param mixed|null $defaultValue
     */
    public function __construct(?Parser $parser, mixed $defaultValue = null)
    {
        $this->parser = $parser;
        $this->defaultValue = $defaultValue;
    }


    /**
     * Create corresponding configuration key
     * @param ConfigName|string $name
     * @param bool $isRequired
     * @param string|null $desc
     * @return ConfigKey<T>
     */
    public function createKey(ConfigName|string $name, bool $isRequired, ?string $desc = null) : ConfigKey
    {
        return ConfigKey::create($name, $isRequired, $this->parser, !$isRequired ? $this->defaultValue : null, $desc);
    }


    /**
     * Create a chain redirection
     * @param callable(T|null):TR $fn
     * @return ConfigRedirect<TR>
     * @template TR
     */
    public function chain(callable $fn) : ConfigRedirect
    {
        $newParser = ClosureParser::create(function (mixed $value, ?string $hintName) use ($fn) : mixed {
            $selection = $this->parser->parse($value, $hintName);
            return $fn($selection);
        });

        return new static($newParser, $this->defaultValue);
    }


    /**
     * Representation of invalid configuration
     * @return static
     */
    public static function invalid() : static
    {
        $invalidParser = ClosureParser::create(function (mixed $value, ?string $hintName) {
            _used($value, $hintName);
            throw new ParseFailedException(_l('Redirection is not supported'));
        });

        return new static($invalidParser, '?');
    }
}