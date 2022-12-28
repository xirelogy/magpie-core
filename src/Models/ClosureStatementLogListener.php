<?php

namespace Magpie\Models;

use Closure;
use Magpie\Models\Concepts\StatementLogListenable;

/**
 * Listen to log query statements and defer to a closure
 */
class ClosureStatementLogListener implements StatementLogListenable
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
    public function logStatement(RawStatement $statement) : void
    {
        ($this->fn)($statement);
    }


    /**
     * Create an instance
     * @param callable(RawStatement):void $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}