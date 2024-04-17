<?php

namespace Magpie\Facades\FileSystem\Options;

use Magpie\General\Traits\StaticCreatable;

/**
 * Allow read operation to be lazy
 */
class FileSystemLazyReadOption extends FileSystemReadWriteOption
{
    use StaticCreatable;

    /**
     * Current type class
     */
    public const TYPECLASS = 'lazy-read';


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }
}