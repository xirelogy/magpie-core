<?php

namespace Magpie\HttpServer\Renderers;

use Closure;
use Magpie\HttpServer\CommonRenderable;

/**
 * Implementation of Renderable using closure
 */
final class ClosureRenderer extends CommonRenderable
{
    /**
     * @var Closure Redirecting closure
     */
    protected Closure $fn;


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
    protected function onRender() : void
    {
        ($this->fn)();
    }


    /**
     * Create instance
     * @param callable():void $fn
     * @return static
     */
    public static function for(callable $fn) : static
    {
        return new static($fn);
    }
}