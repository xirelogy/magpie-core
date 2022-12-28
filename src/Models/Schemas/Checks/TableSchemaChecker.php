<?php

namespace Magpie\Models\Schemas\Checks;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\General\Traits\StaticClass;
use Magpie\Models\Concepts\ModelCheckListenable;
use Magpie\Models\Impls\DefaultModelCheckListener;
use Magpie\Models\Model;
use ReflectionException;

/**
 * Table schema checker basics
 */
abstract class TableSchemaChecker
{
    use StaticClass;


    /**
     * Apply check on given model
     * @param Model $model
     * @param ModelCheckListenable|null $listener
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws ReflectionException
     */
    public abstract static function apply(Model $model, ?ModelCheckListenable $listener = null) : void;


    /**
     * Apply check on given model
     * @param Model $model
     * @param ModelCheckListenable $listener
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     * @throws ReflectionException
     */
    protected static abstract function applyOn(Model $model, ModelCheckListenable $listener) : void;


    /**
     * Make sure listener is available
     * @param ModelCheckListenable|null $listener
     * @return ModelCheckListenable
     */
    protected static function acceptListener(?ModelCheckListenable $listener) : ModelCheckListenable
    {
        return $listener ?? new DefaultModelCheckListener();
    }
}