<?php

namespace Magpie\Objects\Traits;

/**
 * Model properties naming using the 'FirstCaps' convention
 * @requires \Magpie\Objects\ModeledObject
 */
trait CommonFirstCapsModelName
{
    /**
     * @var string Property name for 'Id'
     */
    protected static string $_traitNameOf_Id = 'Id';
    /**
     * @var string Property name for 'CreatedAt'
     */
    protected static string $_traitNameOf_CreatedAt = 'CreatedAt';
    /**
     * @var string Property name for 'UpdatedAt'
     */
    protected static string $_traitNameOf_UpdatedAt = 'UpdatedAt';
    /**
     * @var string Property name for 'DeletedAt'
     */
    protected static string $_traitNameOf_DeletedAt = 'DeletedAt';
}