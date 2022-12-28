<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to type class not supported
 */
class UnsupportedTypeClassException extends UnsupportedException
{
    /**
     * Constructor
     * @param string $typeClass Type class that was not supported
     * @param string $baseClassName The base class that the type class should be based on
     * @param Throwable|null $previous
     */
    public function __construct(string $typeClass, string $baseClassName, ?Throwable $previous = null)
    {
        $message = static::formatMessage($typeClass, $baseClassName);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $typeClass
     * @param string $baseClassName
     * @return string
     */
    protected static function formatMessage(string $typeClass, string $baseClassName) : string
    {
        return _format_safe(_l('\'{{1}}\' cannot create instance of the \'{{0}}\' type class'), $typeClass, $baseClassName)
            ?? _l('Cannot create instance of given type class');
    }
}