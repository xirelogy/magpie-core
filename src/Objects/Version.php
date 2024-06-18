<?php

namespace Magpie\Objects;

use Magpie\Codecs\Concepts\PreferStringable;

/**
 * Common version
 */
abstract class Version implements PreferStringable
{
    /**
     * Compare two versions for precedences
     * @param Version $rhs
     * @return int|null <0 if current version prior to RHS, 0 if both versions matches, >0 if current version after RHS, null if unsupported
     */
    public abstract function compare(Version $rhs) : int|null;
}