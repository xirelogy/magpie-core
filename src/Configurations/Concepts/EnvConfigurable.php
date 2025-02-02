<?php

namespace Magpie\Configurations\Concepts;

use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * May be configured from environment variables
 */
interface EnvConfigurable
{
    /**
     * Create instance by parsing from environment variables
     * @param string|null ...$prefixes
     * @return static
     * @throws SafetyCommonException
     * @throws ArgumentException
     */
    public static function fromEnv(string|null ...$prefixes) : static;
}