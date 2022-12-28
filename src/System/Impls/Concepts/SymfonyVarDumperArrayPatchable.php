<?php

namespace Magpie\System\Impls\Concepts;

/**
 * May patch the array for Symfony's VarDumper cast
 * @internal
 */
interface SymfonyVarDumperArrayPatchable
{
    /**
     * If the item in given array shall be kept
     * @param string $key
     * @return bool
     */
    public function isKeepArrayItem(string $key) : bool;


    /**
     * Patch the item in given array
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function patchArrayItem(string $key, mixed &$value) : void;
}