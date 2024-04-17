<?php

namespace Magpie\Exceptions;

use Magpie\Locales\Concepts\Localizable;
use Throwable;

/**
 * Class cannot be anonymous
 */
class ClassCannotAnonymousException extends SafetyCommonException
{
    /**
     * Constructor
     * @param Localizable|string|null $typename
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(Localizable|string|null $typename = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = static::formatMessage($typename);

        parent::__construct($message, $previous, $code);
    }


    /**
     * Format message
     * @param Localizable|string|null $typename
     * @return string
     */
    protected static function formatMessage(Localizable|string|null $typename) : string
    {
        if ($typename === null) return _l('Cannot be anonymous class');

        return _format_l('Cannot be anonymous class', '\'{{0}}\' cannot be anonymous class', $typename);
    }
}