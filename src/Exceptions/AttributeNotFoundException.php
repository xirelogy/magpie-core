<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to expected attribute not found
 */
class AttributeNotFoundException extends SafetyCommonException
{
    /**
     * Constructor
     * @param string $attributeClass
     * @param Throwable|null $previous
     */
    public function __construct(string $attributeClass, ?Throwable $previous = null)
    {
        $message = static::formatMessage($attributeClass);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $attributeClass
     * @return string
     */
    protected static function formatMessage(string $attributeClass) : string
    {
        return _format_safe(_l('Required attribute of class \'{{0}}\' not found'), $attributeClass)
            ?? _l('Required attribute not found');
    }
}