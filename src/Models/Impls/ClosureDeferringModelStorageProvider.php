<?php

namespace Magpie\Models\Impls;

use Closure;
use Magpie\Models\Concepts\ModelStorageProvidable;
use Magpie\Models\Model;
use Magpie\Models\Schemas\TableSchema;

/**
 * A model storage provider that is deferring to a closure
 * @internal
 */
class ClosureDeferringModelStorageProvider extends DeferringModelStorageProvider
{
    /**
     * @var Closure Deferred closure
     */
    protected readonly Closure $fn;


    /**
     * Constructor
     * @param TableSchema $tableSchema
     * @param callable(Model,string):ModelStorageProvidable $fn
     */
    public function __construct(TableSchema $tableSchema, callable $fn)
    {
        parent::__construct($tableSchema);

        $this->fn = $fn;
    }


    /**
     * @inheritDoc
     */
    public function defer(Model $instance, string $connection) : ModelStorageProvidable
    {
        return ($this->fn)($instance, $connection);
    }
}