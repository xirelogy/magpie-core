<?php

namespace Magpie\Models;

/**
 * Map multiple models
 */
class ModelMap extends BaseModelAccessor
{
    /**
     * Construct from given models
     * @param iterable<Model> $models
     * @return static
     */
    public static function for(iterable $models) : static
    {
        $retModels = [];
        foreach ($models as $model) {
            $retModels[$model::class] = $model;
        }

        return new static($retModels);
    }
}