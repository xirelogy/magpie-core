<?php

namespace Magpie\System\Impls\Patches;

use Magpie\System\Impls\Concepts\SymfonyVarDumperArrayPatchable;

/**
 * Drop all private properties
 * @internal
 */
class SymfonyVarDumperDropPrivatePatch implements SymfonyVarDumperArrayPatchable
{
    /**
     * @var string Target class name
     */
    protected string $targetClassName;


    /**
     * Constructor
     * @param string $targetClassName
     */
    protected function __construct(string $targetClassName)
    {
        $this->targetClassName = $targetClassName;
    }


    /**
     * @inheritDoc
     */
    public function isKeepArrayItem(string $key) : bool
    {
        return !str_starts_with($key, "\0" . $this->targetClassName . "\0");
    }


    /**
     * @inheritDoc
     */
    public function patchArrayItem(string $key, mixed &$value) : void
    {
        // NOP
    }


    /**
     * Create instance for given target class
     * @param string $targetClassName
     * @return static
     */
    public static function for(string $targetClassName) : static
    {
        return new static($targetClassName);
    }
}