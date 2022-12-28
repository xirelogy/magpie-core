<?php

namespace Magpie\Models\Impls;

use Closure;
use Magpie\Models\Concepts\Modelable;
use Magpie\Models\Schemas\TableSchema;

/**
 * May finalize into model using deferred closure
 */
class ClosureModelFinalizer extends ModelFinalizer
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
    public function finalize(array $values) : Modelable
    {
        if ($this->isCurrentAllColumnsSelected) {
            // All columns for current model selected, use the default hydration function
            return ($this->fn)($values, $this->casts);
        } else {
            // Use temporary model for hydration
            $tableSchema = TableSchema::from(TemporaryModel::class);
            $storage = ModelBinder::hydrateStorage($tableSchema, $values, $this->casts);
            return new TemporaryModel($storage);
        }
    }


    /**
     * Create an instance
     * @param callable(array,array):Modelable $fn
     * @return static
     */
    public static function create(callable $fn) : static
    {
        return new static($fn);
    }
}