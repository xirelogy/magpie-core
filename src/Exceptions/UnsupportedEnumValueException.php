<?php

namespace Magpie\Exceptions;

use BackedEnum;
use Throwable;

/**
 * Exception due to unsupported value for backed enum
 */
class UnsupportedEnumValueException extends UnsupportedValueException
{
    /**
     * @var class-string<BackedEnum> The specific enum class where value not supported
     */
    public readonly string $enumClassName;


    /**
     * Constructor
     * @param string|int $target
     * @param class-string<BackedEnum> $enumClassName
     * @param Throwable|null $previous
     */
    public function __construct(string|int $target, string $enumClassName, ?Throwable $previous = null)
    {
        parent::__construct($target, _l('enum value'), $previous);

        $this->enumClassName = $enumClassName;
    }
}