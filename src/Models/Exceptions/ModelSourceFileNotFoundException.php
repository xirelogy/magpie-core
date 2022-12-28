<?php

namespace Magpie\Models\Exceptions;

use Throwable;

/**
 * Exception due to source file of given model not found
 */
class ModelSourceFileNotFoundException extends ModelSafetyException
{
    /**
     * Constructor
     * @param string $modelClassName
     * @param Throwable|null $previous
     */
    public function __construct(string $modelClassName, ?Throwable $previous = null)
    {
        $message = static::formatMessage($modelClassName);

        parent::__construct($message, $previous);
    }


    /**
     * Format message
     * @param string $modelClassName
     * @return string
     */
    protected static function formatMessage(string $modelClassName) : string
    {
        return _format_safe(_l('Source file for {{0}} not found'), $modelClassName) ??
            _l('Source file for model not found');
    }
}