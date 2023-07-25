<?php

namespace Magpie\System\Impls\Patches;

use Magpie\System\Impls\Concepts\SymfonyVarDumperArrayPatchable;

/**
 * Apply stub to target property
 * @internal
 */
abstract class SymfonyVarDumperStubPatch implements SymfonyVarDumperArrayPatchable
{
    /**
     * @var string Target key
     */
    protected string $targetKey;


    /**
     * Constructor
     * @param string $targetKey
     */
    protected function __construct(string $targetKey)
    {
        $this->targetKey = $targetKey;
    }


    /**
     * @inheritDoc
     */
    public function isKeepArrayItem(string $key) : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function patchArrayItem(string $key, mixed &$value) : void
    {
        if ($key !== $this->targetKey) return;

        $this->onPatchArrayItem($key, $value);
    }


    /**
     * Apply patch to target array item
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected abstract function onPatchArrayItem(string $key, mixed &$value) : void;


    /**
     * @inheritDoc
     */
    public function patchReturnArray(mixed $object, array &$values) : void
    {
        // MOP
    }


    /**
     * Create instance for given target key
     * @param string $targetKey
     * @return static
     */
    public static function for(string $targetKey) : static
    {
        return new static($targetKey);
    }
}