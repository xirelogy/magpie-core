<?php

namespace Magpie\System\Impls\Patches;

use Symfony\Component\VarDumper\Caster\CutStub;

/**
 * Apply cut stub to target property
 * @internal
 */
class SymfonyVarDumperCutStubPatch extends SymfonyVarDumperStubPatch
{
    /**
     * @inheritDoc
     */
    protected function onPatchArrayItem(string $key, mixed &$value) : void
    {
        $value = new CutStub($value);
    }
}