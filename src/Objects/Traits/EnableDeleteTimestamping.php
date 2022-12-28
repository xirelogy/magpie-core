<?php

namespace Magpie\Objects\Traits;

use Carbon\Carbon;
use Magpie\Exceptions\GeneralPersistenceException;
use Magpie\Exceptions\NullException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Sugars\Excepts;

/**
 * Enable soft deletion using 'delete timestamp'
 * @requires \Magpie\General\Concepts\Deletable
 * @requires \Magpie\Objects\ModeledObject
 */
trait EnableDeleteTimestamping
{
    /**
     * @var bool Specify that 'delete timestamp' is enabled
     */
    protected static bool $_traitEnabled_DeleteTimestamp = true;


    /**
     * If current object is deleted
     * @return bool
     */
    public final function isDeleted() : bool
    {
        return Excepts::noThrow(fn ()
            => $this->getBaseModel()->{static::$_traitNameOf_DeletedAt} !== null
        , false);
    }


    /**
     * Delete the current item
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public final function delete() : void
    {
        $this->onBeforeDelete();

        $this->getBaseModel()->{static::$_traitNameOf_DeletedAt} = Carbon::now();
        $this->save();

        $this->onAfterDelete();
    }


    /**
     * Handling before deletion
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    protected function onBeforeDelete() : void
    {
        _throwable(1) ?? throw new NullException();
        _throwable(2) ?? throw new GeneralPersistenceException();
    }


    /**
     * Handling after deletion
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    protected function onAfterDelete() : void
    {
        _throwable(1) ?? throw new NullException();
        _throwable(2) ?? throw new GeneralPersistenceException();
    }
}