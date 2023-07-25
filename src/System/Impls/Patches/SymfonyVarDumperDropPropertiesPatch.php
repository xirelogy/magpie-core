<?php

namespace Magpie\System\Impls\Patches;

use Magpie\System\Impls\Concepts\SymfonyVarDumperArrayPatchable;
use Symfony\Component\VarDumper\Caster\Caster;

/**
 * Drop all listed properties
 * @internal
 */
class SymfonyVarDumperDropPropertiesPatch implements SymfonyVarDumperArrayPatchable
{
    /**
     * @var array<string, string> Target properties to be dropped
     */
    protected array $properties;


    /**
     * Constructor
     * @param array<string, string> $properties
     */
    protected function __construct(array $properties)
    {
        $this->properties = $properties;
    }


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


    /**
     * Create instance for given target properties
     * @param string ...$properties
     * @return static
     */
    public static function for(string ...$properties) : static
    {
        $ret = [];
        foreach ($properties as $property) {
            $ret[$property] = $property;
        }

        return new static($ret);
    }
}