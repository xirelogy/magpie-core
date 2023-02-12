<?php

namespace Magpie\Codecs\Formats;

use Closure;

/**
 * Format from specific closure
 */
class ClosureFormatter implements Formatter
{
    /**
     * @var Closure Delegated closure function
     */
    protected Closure $fn;


    /**
     * @param callable(mixed):mixed $fn
     */
    protected function __construct(callable $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public final function format(mixed $value) : mixed
    {
        return ($this->fn)($value);
    }


    /**
     * Create an instance
     * @param callable(mixed):mixed $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}