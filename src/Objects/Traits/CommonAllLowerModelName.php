<?php

namespace Magpie\Objects\Traits;

/**
 * Model properties naming using the 'all_lower' convention
 * @requires \Magpie\Objects\ModeledObject
 */
trait CommonAllLowerModelName
{
    /**
     * @var string Property name for 'Id'
     */
    protected static string $_traitNameOf_Id = 'id';
    /**
     * @var string Property name for 'CreatedAt'
     */
    protected static string $_traitNameOf_CreatedAt = 'created_at';
    /**
     * @var string Property name for 'UpdatedAt'
     */
    protected static string $_traitNameOf_UpdatedAt = 'updated_at';
    /**
     * @var string Property name for 'DeletedAt'
     */
    protected static string $_traitNameOf_DeletedAt = 'deleted_at';
}