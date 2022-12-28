<?php

namespace Magpie\Codecs\Parsers;

use Closure;
use Magpie\Codecs\Traits\CommonParser;

/**
 * Parse from specific closure
 * @template T
 * @extends Parser<T>
 */
class ClosureParser implements Parser
{
    /** @use CommonParser<T> */
    use CommonParser;


    /**
     * @var Closure Delegated closure function
     */
    protected Closure $fn;


    /**
     * Constructor
     * @param callable(mixed, string|null):T $fn
     */
    protected function __construct(callable $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    protected function onParse(mixed $value, ?string $hintName) : mixed
    {
        return ($this->fn)($value, $hintName);
    }


    /**
     * Create an instance
     * @param callable(mixed, string|null):T $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}