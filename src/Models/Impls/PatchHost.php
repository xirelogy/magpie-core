<?php

namespace Magpie\Models\Impls;

use Carbon\CarbonInterface;
use Magpie\General\Traits\StaticClass;
use Magpie\Models\Concepts\ModelInitializePatchable;
use Magpie\Models\Concepts\ModelTimestampPatchable;

/**
 * Support for patching to Model event
 * @internal
 */
class PatchHost
{
    use StaticClass;

    /**
     * @var ModelInitializePatchable|null Current patch to initializer
     */
    protected static ?ModelInitializePatchable $patchInitializer = null;
    /**
     * @var ModelTimestampPatchable|null Current patch to creation/update timestamp
     */
    protected static ?ModelTimestampPatchable $patchTimestamp = null;


    /**
     * Start listening for model initialization patch
     * @param ModelInitializePatchable $patch
     * @return void
     */
    public static function listenInitializer(ModelInitializePatchable $patch) : void
    {
        static::$patchInitializer = $patch;
    }


    /**
     * Try to initialize a column
     * @param string $tableModelClass
     * @param string $columnName
     * @param mixed|null $result
     * @return bool
     */
    public static function tryInitializeColumn(string $tableModelClass, string $columnName, mixed &$result = null) : bool
    {
        if (static::$patchInitializer === null) return false;

        return static::$patchInitializer->tryInitializeColumn($tableModelClass, $columnName, $result);
    }


    /**
     * Start listening for model timestamp patch
     * @param ModelTimestampPatchable $patch
     * @return void
     */
    public static function listenTimestamp(ModelTimestampPatchable $patch) : void
    {
        static::$patchTimestamp = $patch;
    }


    /**
     * Try to get creation timestamp
     * @param string $tableModelClass Table's model class name
     * @return CarbonInterface|null Corresponding creation timestamp if successful
     */
    public static function tryCreateTimestamp(string $tableModelClass) : ?CarbonInterface
    {
        return static::$patchTimestamp?->tryCreateTimestamp($tableModelClass);
    }


    /**
     * Try to get update timestamp
     * @param string $tableModelClass Table's model class name
     * @return CarbonInterface|null Corresponding update timestamp if successful
     */
    public static function tryUpdateTimestamp(string $tableModelClass) : ?CarbonInterface
    {
        return static::$patchTimestamp?->tryUpdateTimestamp($tableModelClass);
    }
}