<?php

namespace Magpie\General;

use Exception;
use Magpie\Exceptions\OperationFailedException;

/**
 * Regular expression 'as-a' object
 */
class RegEx
{
    /**
     * @var string The pattern string
     */
    public readonly string $pattern;


    /**
     * Constructor
     * @param string $pattern
     */
    protected function __construct(string $pattern)
    {
        $this->pattern = $pattern;
    }


    /**
     * Check if the given subject is matched with this regular expression
     * @param string $subject Subject to be matched
     * @param int $offset
     * @return bool
     * @throws OperationFailedException
     */
    public function isMatched(string $subject, int $offset = 0) : bool
    {
        $matches = [];

        $ret = preg_match($this->pattern, $subject, $matches, offset: $offset);
        static::checkReturn($ret);

        return $ret === 1;
    }


    /**
     * Split the given subject using delimiter matching current regular expression
     * @param string $subject Subject to be split
     * @param int|null $limit Maximum number of substrings to be returned (if specified)
     * @return array<string>
     * @throws OperationFailedException
     */
    public function split(string $subject, ?int $limit = null) : array
    {
        $limit = $limit ?? -1;

        $ret = preg_split($this->pattern, $subject, $limit);
        static::checkReturn($ret);

        return $ret;
    }


    /**
     * Check return value of preg_* functions
     * @param mixed $ret
     * @return void
     * @throws OperationFailedException
     */
    protected static function checkReturn(mixed $ret) : void
    {
        if ($ret !== false) return;

        $ex = new Exception(preg_last_error_msg());
        throw new OperationFailedException(previous: $ex);
    }


    /**
     * Create a regular expression from given pattern string
     * @param string $pattern
     * @return static
     */
    public static function from(string $pattern) : static
    {
        return new static($pattern);
    }


    /**
     * Create a regular expression from simple wildcard pattern string (using asterisk as wildcard)
     * @param string $pattern
     * @return static
     */
    public static function fromWildcard(string $pattern) : static
    {
        // Reference from Str::is()
        // https://github.com/illuminate/support/blob/master/Str.php

        $pattern = preg_quote($pattern, '#');
        $pattern = str_replace('\*', '.*', $pattern);
        $pattern = '#^' . $pattern .'\z#u';

        return static::from($pattern);
    }
}