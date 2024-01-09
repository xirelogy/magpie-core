<?php

namespace Magpie\Models\Providers\Sqlite\Exceptions;

use Magpie\General\Invalid;
use Magpie\General\Sugars\Quote;
use Throwable;

/**
 * Exceptions due to unexpected token during SQLite parsing
 */
class SqliteUnexpectedTokenParserException extends SqliteUnexpectedParserException
{
    /**
     * @var string|Invalid Provided item
     */
    public readonly string|Invalid $provided;
    /**
     * @var string|Invalid|null Expected item
     */
    public readonly string|Invalid|null $expected;


    /**
     * Constructor
     * @param string|Invalid $provided
     * @param string|Invalid|null $expected
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(string|Invalid $provided, string|Invalid|null $expected = null, ?Throwable $previous = null, int $code = 0)
    {
        $message = static::formatMessage($provided, $expected);

        parent::__construct($message, $previous, $code);

        $this->expected = $expected;
        $this->provided = $provided;
    }


    /**
     * Format the message
     * @param string|Invalid $provided
     * @param string|Invalid|null $expected
     * @return string
     */
    protected static function formatMessage(string|Invalid $provided, string|Invalid|null $expected) : string
    {
        if ($expected !== null) {
            return _format_l(
                'Unexpected token while parsing',
                'Unexpected {{0}} while {{1}} is expected during parsing',
                static::formatItem($provided),
                static::formatItem($expected),
            );
        } else {
            return _format_l(
                'Unexpected token while parsing',
                'Unexpected {{0}} during parsing',
                static::formatItem($provided),
            );
        }
    }


    /**
     * Format the target item
     * @param string|Invalid $target
     * @return string
     */
    protected static function formatItem(string|Invalid $target) : string
    {
        if ($target === Invalid::instance()) return _l('<end of stream>');

        return Quote::single($target);
    }
}