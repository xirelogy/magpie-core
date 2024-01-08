<?php

namespace Magpie\System\Impls\Patches;

use Closure;
use Magpie\System\Impls\Concepts\SymfonyVarDumperArrayPatchable;

/**
 * Insert a value as property
 * @internal
 */
class SymfonyVarDumperInsertValuePatch implements SymfonyVarDumperArrayPatchable
{
    /**
     * @var array<string, Closure> Insert functions
     */
    protected array $insertFns;


    /**
     * Constructor
     * @param iterable<string, Closure> $insertFns
     */
    protected function __construct(iterable $insertFns)
    {
        $this->insertFns = iter_flatten($insertFns);
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
        // NOP
    }


    /**
     * @inheritDoc
     */
    public function patchReturnArray(mixed $object, array &$values) : void
    {
        foreach ($this->insertFns as $key => $fn) {
            $values[$key] = $fn($object);
        }
    }


    /**
     * Create instance for given insertions
     * @param iterable<string, callable(mixed):mixed> $insertFns
     * @return static
     */
    public static function for(iterable $insertFns) : static
    {
        return new static($insertFns);
    }
}