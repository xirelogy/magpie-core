<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;

/**
 * Cast provider
 * @template T
 */
interface AttributeCastable
{
    /**
     * Cast from database raw value
     * @param string $key
     * @param mixed $value
     * @return T|null
     * @throws SafetyCommonException
     */
    public static function fromDb(string $key, mixed $value) : mixed;


    /**
     * Cast into database raw value
     * @param string $key
     * @param T|null $value
     * @return mixed
     * @throws SafetyCommonException
     */
    public static function toDb(string $key, mixed $value) : mixed;
}