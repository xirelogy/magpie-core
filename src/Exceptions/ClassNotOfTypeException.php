<?php

namespace Magpie\Exceptions;

use Magpie\General\Tags\ClassNameString;
use Throwable;

/**
 * Exception due to target of given class is not of the type that was expected
 */
class ClassNotOfTypeException extends NotOfTypeException
{
    /**
     * Constructor
     * @param string $targetClassName
     * @param string $type
     * @param Throwable|null $previous
     */
    public function __construct(string $targetClassName, string $type, ?Throwable $previous = null)
    {
        parent::__construct(new ClassNameString($targetClassName), $type, $previous);
    }
}