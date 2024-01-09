<?php

namespace Magpie\Models\Providers\Sqlite\Impls\Parsers;

use Iterator;
use Magpie\General\Invalid;
use Magpie\General\Str;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteInvalidLexStateParserException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteParserException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteTokenStreamFullException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteUnexpectedAnyTokenParserException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteUnexpectedDataParserException;
use Magpie\Models\Providers\Sqlite\Exceptions\SqliteUnexpectedTokenParserException;

/**
 * Stream of tokens from lexical processing of statements
 * @internal
 */
class SqliteTokenStream
{
    /**
     * @var Iterator Current cursor
     */
    protected Iterator $cursor;
    /**
     * @var string|null Put back cache
     */
    protected ?string $cached = null;


    /**
     * Constructor
     * @param iterable<string> $tokens
     */
    protected function __construct(iterable $tokens)
    {
        $this->cursor = iter_cursor($tokens);
        $this->cursor->rewind();
    }


    /**
     * Peek for the next token (without removing)
     * @return string|null
     */
    public final function peek() : ?string
    {
        if ($this->cached !== null) return $this->cached;

        if (!$this->cursor->valid()) return null;

        // Get value and advance cursor
        $ret = $this->cursor->current();
        $this->cursor->next();

        $this->cached = $ret;
        return $ret;
    }


    /**
     * The next token
     * @return string|null
     */
    public final function next() : ?string
    {
        if ($this->cached !== null) {
            $cached = $this->cached;
            $this->cached = null;
            return $cached;
        }

        if (!$this->cursor->valid()) return null;

        // Get value and advance cursor
        $ret = $this->cursor->current();
        $this->cursor->next();

        return $ret;
    }


    /**
     * Rewind the stream and return the token
     * @param string $token
     * @return void
     * @throws SqliteParserException
     */
    public final function rewind(string $token) : void
    {
        if ($this->cached !== null) throw new SqliteTokenStreamFullException();
        $this->cached = $token;
    }


    /**
     * Expect a next token
     * @param string|null $expected
     * @return string
     * @throws SqliteParserException
     */
    public function expectNext(?string $expected = null) : string
    {
        $ret = $this->next();
        if ($ret === null) throw new SqliteUnexpectedTokenParserException(Invalid::instance(), $expected);

        return $ret;
    }


    /**
     * Expect end of stream
     * @return void
     * @throws SqliteParserException
     */
    public function expectEnd() : void
    {
        $ret = $this->peek();
        if($ret === null) return;

        throw new SqliteUnexpectedTokenParserException($ret, Invalid::instance());
    }


    /**
     * Expect a name as next token
     * @return string
     * @throws SqliteParserException
     */
    public function expectName() : string
    {
        // TODO: may need to further check if name is valid
        return $this->expectNameUnquote();
    }


    /**
     * Expect a name as next token, removing quotes if necessary
     * @return string
     * @throws SqliteParserException
     */
    private function expectNameUnquote() : string
    {
        $name = $this->expectNext();

        if (str_starts_with($name, '\'') && str_ends_with($name, '\'')) return substr($name, 1, -1);
        if (str_starts_with($name, '[') && str_ends_with($name, ']')) return substr($name, 1, -1);
        if (str_starts_with($name, '"') && str_ends_with($name, '"')) return substr($name, 1, -1);
        if (str_starts_with($name, '`') && str_ends_with($name, '`')) return substr($name, 1, -1);

        return $name;
    }


    /**
     * Expect a signed number-literal
     * @return int
     * @throws SqliteParserException
     */
    public function expectSignNumberLiteral() : int
    {
        $isPositive = null;
        if ($this->optionalToken('+')) {
            // Positive
            $isPositive = true;
        } else if ($this->optionalToken('-')) {
            // Negative
            $isPositive = false;
        }

        $payload = $this->expectNext();
        if (!is_numeric($payload)) throw new SqliteUnexpectedDataParserException(_l('not a number'));

        $ret = intval($payload);
        if ($isPositive === false) $ret = -$ret;

        return $ret;
    }


    /**
     * Expect a specific keyword
     * @param string $keyword
     * @return string
     * @throws SqliteParserException
     */
    public function expectKeyword(string $keyword) : string
    {
        return $this->onExpect($keyword, static::compareKeyword(...));
    }


    /**
     * Any of the keyword is expected
     * @param string ...$keywords
     * @return string|null
     * @throws SqliteParserException
     */
    public function expectAnyKeyword(string ...$keywords) : ?string
    {
        foreach ($keywords as $keyword) {
            $test = $this->optionalKeyword($keyword);
            if ($test !== null) return $test;
        }

        $provided = $this->peek() ?? Invalid::instance();

        throw new SqliteUnexpectedAnyTokenParserException($provided, $keywords);
    }


    /**
     * Any of the keyword is optional
     * @param string ...$keywords
     * @return string|null
     * @throws SqliteParserException
     */
    public function optionalAnyKeyword(string ...$keywords) : ?string
    {
        foreach ($keywords as $keyword) {
            $test = $this->optionalKeyword($keyword);
            if ($test !== null) return $test;
        }

        return null;
    }


    /**
     * Allow an optional keyword
     * @param string $keyword
     * @return string|null
     * @throws SqliteParserException
     */
    public function optionalKeyword(string $keyword) : ?string
    {
        return $this->onOptional($keyword, static::compareKeyword(...));
    }


    /**
     * Allow an optional keyword (with boolean result)
     * @param string $keyword
     * @return bool
     * @throws SqliteParserException
     */
    public function ifOptionalKeyword(string $keyword) : bool
    {
        return $this->optionalKeyword($keyword) !== null;
    }


    /**
     * Expect a specific token
     * @param string $token
     * @return string
     * @throws SqliteParserException
     */
    public function expectToken(string $token) : string
    {
        return $this->onExpect($token, static::compareToken(...));
    }


    /**
     * Any of the token is optional
     * @param string ...$tokens
     * @return string|null
     * @throws SqliteParserException
     */
    public function optionalAnyToken(string ...$tokens) : ?string
    {
        foreach ($tokens as $token) {
            $test = $this->optionalToken($token);
            if ($test !== null) return $test;
        }

        return null;
    }


    /**
     * Allow an optional token
     * @param string $token
     * @return string|null
     * @throws SqliteParserException
     */
    public function optionalToken(string $token) : ?string
    {
        return $this->onOptional($token, static::compareToken(...));
    }


    /**
     * Allow an optional token (with boolean result)
     * @param string $token
     * @return bool
     * @throws SqliteParserException
     */
    public function ifOptionalToken(string $token) : bool
    {
        return $this->optionalToken($token) !== null;
    }


    /**
     * Expect a specific item
     * @param string $expected
     * @param callable(string,string):bool $comparerFn
     * @return string
     * @throws SqliteParserException
     */
    protected final function onExpect(string $expected, callable $comparerFn) : string
    {
        $ret = $this->expectNext($expected);
        if ($comparerFn($expected, $ret)) return $ret;

        $this->rewind($ret);
        throw new SqliteUnexpectedTokenParserException($ret, $expected);
    }


    /**
     * Allow an optional item
     * @param string $expected
     * @param callable(string,string):bool $comparerFn
     * @return string|null
     * @throws SqliteParserException
     */
    protected final function onOptional(string $expected, callable $comparerFn) : ?string
    {
        $ret = $this->next();
        if ($ret === null) return null;

        if ($comparerFn($expected, $ret)) return $ret;

        $this->rewind($ret);
        return null;
    }


    /**
     * Compare keyword equivalence
     * @param string $expected
     * @param string $provided
     * @return bool
     */
    private static function compareKeyword(string $expected, string $provided) : bool
    {
        return strtoupper($expected) === strtoupper($provided);
    }


    /**
     * Compare token equivalence
     * @param string $expected
     * @param string $provided
     * @return bool
     */
    private static function compareToken(string $expected, string $provided) : bool
    {
        return $expected === $provided;
    }


    /**
     * Construct from SQL
     * @param string $sql
     * @return static
     * @throws SqliteParserException
     */
    public static function from(string $sql) : static
    {
        $tokens = static::getTokens($sql);
        return new static($tokens);
    }


    /**
     * Lex state: initial/space, expecting content
     */
    private const STATE_INIT = 0;
    /**
     * Lex state: content
     */
    private const STATE_CONTENT = 1;
    /**
     * In single quote
     */
    private const STATE_QUOTE_SINGLE = 2;
    /**
     * In single quote, ready to end
     */
    private const STATE_QUOTE_SINGLE_ENDING = 3;
    /**
     * In double quote
     */
    private const STATE_QUOTE_DOUBLE = 4;
    /**
     * In backtick quote
     */
    private const STATE_QUOTE_BACKTICK = 5;
    /**
     * In square bracket quote
     */
    private const STATE_QUOTE_SQUARE = 6;


    /**
     * Convert SQLite statement into tokens
     * @param string $sql
     * @return iterable<string>
     * @throws SqliteParserException
     */
    private static function getTokens(string $sql) : iterable
    {
        $state = static::STATE_INIT;
        $buffer = '';

        $i = 0;
        $length = strlen($sql);

        while ($i < $length) {
            $ch = substr($sql, $i, 1);
            switch ($state) {
                case static::STATE_INIT:
                    // Handle spaces
                    switch ($ch) {
                        case ' ':
                        case "\t":
                        case "\r":
                        case "\n":
                            // Consume and ignore
                            ++$i;
                            break;
                        default:
                            // Change state and reparse
                            $state = static::STATE_CONTENT;
                            break;
                    }
                    break;

                case static::STATE_CONTENT:
                    // Expecting content
                    switch ($ch) {
                        case ' ':
                        case "\t":
                        case "\r":
                        case "\n":
                            // Spaces, break away
                            $captured = static::captureTokenBuffer($buffer);
                            if ($captured !== null) yield $captured;

                            $state = static::STATE_INIT;
                            break;

                        case '*':
                        case '+':
                        case '-':
                        case ',':
                        case '.':
                        case '(':
                        case ')':
                        case ';':
                            // These symbols are treated as token
                            $captured = static::captureTokenBuffer($buffer);
                            if ($captured !== null) yield $captured;

                            yield $ch;
                            ++$i;
                            $state = static::STATE_INIT;
                            break;

                        case '\'':
                            // 'name'
                            $buffer = '\'';
                            ++$i;
                            $state = static::STATE_QUOTE_SINGLE;
                            break;

                        case '"':
                            // "name"
                            $buffer = '"';
                            ++$i;
                            $state = static::STATE_QUOTE_DOUBLE;
                            break;

                        case '`':
                            // `name`
                            $buffer = '`';
                            ++$i;
                            $state = static::STATE_QUOTE_BACKTICK;
                            break;

                        case '[':
                            // [name]
                            $buffer = '[';
                            ++$i;
                            $state = static::STATE_QUOTE_SQUARE;
                            break;

                        default:
                            // Expecting an identifier
                            // TODO: Check for valid identifier characters (A-Z/a-z/0-9/_/$)
                            $buffer .= $ch;
                            ++$i;
                            break;

                    }
                    break;

                case static::STATE_QUOTE_SINGLE:
                    // In single quote
                    $buffer .= $ch;
                    ++$i;

                    if ($ch == '\'') {
                        $state = static::STATE_QUOTE_SINGLE_ENDING;
                    }
                    break;

                case static::STATE_QUOTE_SINGLE_ENDING:
                    // In single quote, ending
                    if ($ch == '\'') {
                        // Escaped single quote, return to STATE_QUOTE_SINGLE without buffer appending
                        ++$i;
                        $state = static::STATE_QUOTE_SINGLE;
                    } else {
                        $captured = static::captureTokenBuffer($buffer);
                        if ($captured !== null) yield $captured;

                        $state = static::STATE_INIT;
                    }
                    break;

                case static::STATE_QUOTE_DOUBLE:
                    // In double quote
                    $buffer .= $ch;
                    ++$i;

                    if ($ch == '"') {
                        $captured = static::captureTokenBuffer($buffer);
                        if ($captured !== null) yield $captured;
                        $state = static::STATE_INIT;
                    }
                    break;

                case static::STATE_QUOTE_BACKTICK:
                    // In backtick
                    $buffer .= $ch;
                    ++$i;

                    if ($ch == '`') {
                        $captured = static::captureTokenBuffer($buffer);
                        if ($captured !== null) yield $captured;
                        $state = static::STATE_INIT;
                    }
                    break;

                case static::STATE_QUOTE_SQUARE:
                    // In square bracket
                    $buffer .= $ch;
                    ++$i;

                    if ($ch == ']') {
                        $captured = static::captureTokenBuffer($buffer);
                        if ($captured !== null) yield $captured;
                        $state = static::STATE_INIT;
                    }
                    break;

                default:
                    // Bad state
                    throw new SqliteInvalidLexStateParserException($state);
            }
        }

        // Exit check
        $captured = static::captureTokenBuffer($buffer);
        if ($captured !== null) yield $captured;
    }


    /**
     * Capture token buffer if available
     * @param string $buffer
     * @return string|null
     */
    private static function captureTokenBuffer(string &$buffer) : ?string
    {
        $ret = null;
        if (!Str::isNullOrEmpty($buffer)) $ret = $buffer;
        $buffer = '';

        return $ret;
    }
}