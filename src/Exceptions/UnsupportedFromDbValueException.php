<?php

namespace Magpie\Exceptions;

use Throwable;

/**
 * Exception due to unsupported value when casting from database
 */
class UnsupportedFromDbValueException extends UnsupportedValueException
{
    /**
     * Constructor
     * @param mixed $target
     * @param Throwable|null $previous
     */
    public function __construct(mixed $target, ?Throwable $previous = null)
    {
        parent::__construct($target, _l('cast from database'), $previous);
    }
}