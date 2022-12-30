<?php

namespace Magpie\Codecs\Parsers;

use Magpie\Codecs\Traits\CommonParser;
use Magpie\Exceptions\UnsupportedValueException;

/**
 * A fixed parser that only accepts the given value
 * @implements Parser<T>
 * @template T
 */
class FixedParser implements Parser
{
    /** @use CommonParser<T> */
    use CommonParser;

    /**
     * @var array<T> Allowable values
     */
    protected array $values;


    /**
     * Constructor
     * @param array<T> $values
     */
    protected function __construct(array $values)
    {
        $this->values = $values;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : mixed
    {
        if (!in_array($value, $this->values)) throw new UnsupportedValueException($value);

        return $value;
    }


    /**
     * Create an instance
     * @param T ...$values Supported value(s)
     * @return static
     */
    public static function create(mixed ...$values) : static
    {
        return new static($values);
    }
}