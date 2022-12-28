<?php

namespace Magpie\System\Concepts;

use Exception;

/**
 * Exception that can be localized across execution context
 */
interface ExceptionContextLocalizable
{
    /**
     * Localize current exception
     * @return Exception
     */
    public function exceptionLocalize() : Exception;
}