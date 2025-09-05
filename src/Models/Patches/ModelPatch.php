<?php

namespace Magpie\Models\Patches;

use Magpie\General\Traits\StaticClass;
use Magpie\Models\Concepts\ModelInitializePatchable;
use Magpie\Models\Concepts\ModelSavePatchable;
use Magpie\Models\Concepts\ModelTimestampPatchable;
use Magpie\Models\Impls\PatchHost;

/**
 * Support patching the model system
 */
class ModelPatch
{
    use StaticClass;


    /**
     * Start listening for model initialization patch
     * @param ModelInitializePatchable $patch
     * @return void
     */
    public static function listenInitializer(ModelInitializePatchable $patch) : void
    {
        PatchHost::listenInitializer($patch);
    }


    /**
     * Start listening for model timestamp patch
     * @param ModelTimestampPatchable $patch
     * @return void
     */
    public static function listenTimestamp(ModelTimestampPatchable $patch) : void
    {
        PatchHost::listenTimestamp($patch);
    }


    /**
     * Start listening for model save patch
     * @param ModelSavePatchable $patch
     * @return void
     */
    public static function listenSave(ModelSavePatchable $patch) : void
    {
        PatchHost::listenSave($patch);
    }
}