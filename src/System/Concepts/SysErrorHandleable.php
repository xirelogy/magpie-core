<?php

namespace Magpie\System\Concepts;

use Throwable;

/**
 * May handle system error (PHP's native set_error_handler)
 */
interface SysErrorHandleable
{
    /**
     * Handle PHP native error
     * @param int $errNo
     * @param string $errStr
     * @param string $errFile
     * @param int $errLine
     * @return bool
     * @throws Throwable
     */
    public function onError(int $errNo, string $errStr, string $errFile, int $errLine) : bool;
}