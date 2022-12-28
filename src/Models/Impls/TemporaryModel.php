<?php

namespace Magpie\Models\Impls;

use Magpie\Models\Annotations\Table;
use Magpie\Models\Model;

/**
 * Temporary model with dynamic attributes
 * @internal
 */
#[
    Table('~')
]
class TemporaryModel extends Model
{
    /**
     * @inheritDoc
     */
    public static function getConnectionName() : string
    {
        return '';
    }
}