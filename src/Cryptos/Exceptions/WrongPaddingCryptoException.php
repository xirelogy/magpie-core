<?php

namespace Magpie\Cryptos\Exceptions;

use Throwable;

/**
 * Wrong padding while decoding
 */
class WrongPaddingCryptoException extends CryptoException
{
    /**
     * @var string The corresponding padding's type class
     */
    public readonly string $paddingTypeClass;


    /**
     * Constructor
     * @param string $paddingTypeClass
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(string $paddingTypeClass, ?Throwable $previous = null, int $code = 0)
    {
        $message = static::formatMessage($paddingTypeClass);

        parent::__construct($message, $previous, $code);

        $this->paddingTypeClass = $paddingTypeClass;
    }


    /**
     * Format the message
     * @param string $paddingTypeClass
     * @return string
     */
    protected static function formatMessage(string $paddingTypeClass) : string
    {
        return _format_safe(_l('Wrong "{{0}}" padding while decoding'), $paddingTypeClass) ??
            _l('Wrong padding while decoding');
    }
}