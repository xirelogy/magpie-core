<?php

namespace Magpie\Cryptos\Exceptions;

use Throwable;

/**
 * Exception due to invalid bit size
 */
class InvalidBitSizeException extends CryptoException
{
    /**
     * @var int The provided bit size
     */
    public readonly int $providedBitSize;
    /**
     * @var int|null The expected bit size (if any)
     */
    public readonly ?int $expectedBitSize;


    /**
     * Constructor
     * @param int $providedBitSize
     * @param string|null $subject
     * @param int|null $expectedBitSize
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(int $providedBitSize, ?string $subject = null, ?int $expectedBitSize = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = static::formatMessage($providedBitSize, $subject, $expectedBitSize);

        parent::__construct($message, $previous, $code);

        $this->providedBitSize = $providedBitSize;
        $this->expectedBitSize = $expectedBitSize;
    }


    /**
     * Format the message
     * @param int $providedBitSize
     * @param string|null $subject
     * @param int|null $expectedBitSize
     * @return string
     */
    protected static function formatMessage(int $providedBitSize, ?string $subject, ?int $expectedBitSize) : string
    {
        if ($subject !== null) {
            if ($expectedBitSize !== null) {
                return _format_safe(_l('Provided {{1}} bit size {{0}} is invalid, {{2}} expected'), $providedBitSize, $subject, $expectedBitSize) ?? _l('Provided bit size is invalid');
            } else {
                return _format_safe(_l('Provided {{1}} bit size {{0}} is invalid'), $providedBitSize, $subject) ?? _l('Provided bit size is invalid');
            }
        }

        if ($expectedBitSize !== null) {
            return _format_safe(_l('Provided bit size {{0}} is invalid, {{1}} expected'), $providedBitSize, $expectedBitSize) ?? _l('Provided bit size is invalid');
        }

        return _format_safe(_l('Provided bit size {{0}} is invalid'), $providedBitSize) ?? _l('Provided bit size is invalid');
    }
}