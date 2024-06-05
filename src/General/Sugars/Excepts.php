<?php

namespace Magpie\General\Sugars;

use Exception;
use Magpie\Exceptions\ThrownException;
use Magpie\General\Traits\StaticClass;
use Throwable;

/**
 * Exceptions related wrappers
 */
class Excepts
{
    use StaticClass;


    /**
     * Execute ignoring all exceptions
     * @param callable():T $fn
     * @param T|null $defaultReturn
     * @return T
     * @template T
     */
    public static function noThrow(callable $fn, mixed $defaultReturn = null) : mixed
    {
        try {
            return $fn();
        } catch (Throwable) {
            // Ignored with default return
            return $defaultReturn;
        }
    }


    /**
     * Convert any Throwable thrown in scope that is not an Exception to
     * an Exception
     * @param callable():T $fn
     * @return T
     * @throws Exception
     * @template T
     */
    public static function convertThrowable(callable $fn) : mixed
    {
        try {
            return $fn();
        } catch (Exception $ex) {
            throw $ex;
        } catch (Throwable $ex) {
            throw new ThrownException($ex);
        }
    }
}