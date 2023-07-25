<?php

namespace Magpie\System\Impls\Patches;

use Magpie\General\Traits\StaticCreatable;
use Magpie\System\Impls\Concepts\SymfonyVarDumperArrayPatchable;
use Symfony\Component\VarDumper\Caster\Caster;

/**
 * Drop all protected properties
 * @internal
 */
class SymfonyVarDumperDropProtectedPatch implements SymfonyVarDumperArrayPatchable
{
    use StaticCreatable;


    /**
     * @inheritDoc
     */
    public function isKeepArrayItem(string $key) : bool
    {
        return !str_starts_with($key, Caster::PREFIX_PROTECTED);
    }


    /**
     * @inheritDoc
     */
    public function patchArrayItem(string $key, mixed &$value) : void
    {
        // NOP
    }


    /**
     * @inheritDoc
     */
    public function patchReturnArray(mixed $object, array &$values) : void
    {
        // MOP
    }
}