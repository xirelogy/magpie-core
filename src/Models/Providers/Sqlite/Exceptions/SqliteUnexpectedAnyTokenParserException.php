<?php

namespace Magpie\Models\Providers\Sqlite\Exceptions;

use Magpie\General\Invalid;
use Magpie\General\Sugars\Quote;
use Throwable;

/**
 * Exceptions due to unexpected token (within the given any) during SQLite parsing
 */
class SqliteUnexpectedAnyTokenParserException extends SqliteUnexpectedParserException
{
    /**
     * @var string|Invalid Provided item
     */
    public readonly string|Invalid $provided;
    /**
     * @var array<string> Allowed items
     */
    public readonly array $allows;


    /**
     * Constructor
     * @param string|Invalid $provided
     * @param iterable<string> $allows
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(string|Invalid $provided, iterable $allows, ?Throwable $previous = null, int $code = 0)
    {
        $allows = iter_flatten($allows, false);

        $message = static::formatMessage($provided, $allows);

        parent::__construct($message, $previous, $code);

        $this->provided = $provided;
        $this->allows = $allows;
    }


    /**
     * Format the message
     * @param string|Invalid $provided
     * @param array<string> $allows
     * @return string
     */
    protected static function formatMessage(string|Invalid $provided, array $allows) : string
    {
        $retAllows = [];
        foreach ($allows as $allow) {
            $retAllows[] = static::formatItem($allow);
        }

        return _format_l(
            'Unexpected token while parsing',
            'Unexpected {{0}} while any of {{1}} is expected during parsing',
            static::formatItem($provided),
            implode(', ', $retAllows),
        );
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