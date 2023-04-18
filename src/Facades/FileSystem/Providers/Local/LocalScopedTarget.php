<?php

namespace Magpie\Facades\FileSystem\Providers\Local;

use Closure;
use Magpie\General\Concepts\TargetScopeable;
use Magpie\General\Contexts\Scoped;

/**
 * Read/write target for local file with possibility to apply scope
 */
abstract class LocalScopedTarget implements TargetScopeable
{
    /**
     * @var Closure|null Scope function
     */
    protected readonly ?Closure $getScopesFn;


    /**
     * Constructor
     * @param callable():iterable<Scoped>|null $getScopesFn
     * @noinspection PhpDocSignatureInspection
     */
    protected function __construct(?callable $getScopesFn)
    {
        $this->getScopesFn = $getScopesFn;
    }


    /**
     * @inheritDoc
     */
    public function getScopes() : iterable
    {
        if ($this->getScopesFn !== null) yield from ($this->getScopesFn)();
    }
}
