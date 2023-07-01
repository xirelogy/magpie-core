<?php

namespace Magpie\Cryptos\Providers;

use Closure;
use Magpie\Cryptos\Concepts\SymmetricCipherAlgorithmInitializable;

/**
 * Implementation of SymmetricCipherAlgorithmInitializable using closure
 */
class ClosureSymmetricCipherAlgorithmInitializer implements SymmetricCipherAlgorithmInitializable
{
    /**
     * @var Closure Registration function
     */
    protected Closure $fn;


    /**
     * Constructor
     * @param callable():void $fn
     */
    protected function __construct(callable $fn)
    {
        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public function initialize() : void
    {
        ($this->fn)();
    }


    /**
     * Create an instance
     * @param callable():void $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}