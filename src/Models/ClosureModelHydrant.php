<?php

namespace Magpie\Models;

use Closure;
use Magpie\Models\Concepts\ModelHydratable;

/**
 * Implementation of ModelHydratable by forwarding to closures
 * @implements ModelHydratable<T>
 * @template T
 */
class ClosureModelHydrant implements ModelHydratable
{
    /**
     * @var Closure Deferred closure
     */
    protected readonly Closure $fn;


    /**
     * Constructor
     * @param Closure $fn
     */
    protected function __construct(Closure $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public final function hydrate(array $values) : mixed
    {
        return ($this->fn)($values);
    }


    /**
     * Create an instance
     * @param callable(array):T $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}