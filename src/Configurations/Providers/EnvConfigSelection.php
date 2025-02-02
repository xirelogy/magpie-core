<?php

namespace Magpie\Configurations\Providers;

use Magpie\Configurations\Concepts\ConfigSelectable;

/**
 * Configuration selection for 'env'
 */
class EnvConfigSelection implements ConfigSelectable
{
    /**
     * @var array<string|null> All prefixes
     */
    public readonly array $prefixes;


    /**
     * Constructor
     * @param iterable<string, null> $prefixes
     */
    public function __construct(iterable $prefixes)
    {
        $this->prefixes = iter_flatten($prefixes, false);
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return EnvConfigProvider::TYPECLASS;
    }
}