<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Concepts\AttributeCastable;
use Magpie\Models\Concepts\Modelable;
use Magpie\Models\Exceptions\ModelReadException;
use Magpie\Models\Exceptions\ModelWriteException;

/**
 * May finalize into model
 */
abstract class ModelFinalizer
{
    /**
     * @var bool If 'AllColumns' of current model is selected
     */
    protected bool $isCurrentAllColumnsSelected = false;
    /**
     * @var array<string, class-string<AttributeCastable>> Specific cast classes
     */
    protected array $casts = [];


    /**
     * Mark all columns of current model selected
     * @return void
     */
    public function markAllColumnsSelected() : void
    {
        $this->isCurrentAllColumnsSelected = true;
    }


    /**
     * Add specific cast class
     * @param string $columnName
     * @param class-string<AttributeCastable> $castClassName
     * @return void
     */
    public function addCast(string $columnName, string $castClassName) : void
    {
        $this->casts[$columnName] = $castClassName;
    }


    /**
     * Finalize into model
     * @param array<string, mixed> $values
     * @return Modelable
     * @throws SafetyCommonException
     * @throws ModelReadException
     * @throws ModelWriteException
     */
    public abstract function finalize(array $values) : Modelable;
}