<?php

namespace Magpie\HttpServer\Headers;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Magpie\Codecs\Concepts\ObjectParseable;
use Magpie\Codecs\ParserHosts\ArrayCollection;
use Magpie\Codecs\Parsers\BooleanParser;
use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\HttpDateTimeParser;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\General\Invalid;
use Magpie\HttpServer\Cookies\CookieSameSiteAttribute;

/**
 * 'Set-cookie' header value separated by (semi)colon
 * @implements ObjectParseable<SetCookieHeaderValue>
 */
class SetCookieHeaderValue extends ArrayCollection implements ObjectParseable
{
    /**
     * Attribute: expiry time of the cookie
     */
    public const ATTR_EXPIRES = 'expires';
    /**
     * Attribute: Maximum age (in seconds) for the cookie
     */
    public const ATTR_MAX_AGE = 'max-age';
    /**
     * Attribute: Associated domain
     */
    public const ATTR_DOMAIN = 'domain';
    /**
     * Attribute: Associated path
     */
    public const ATTR_PATH = 'path';
    /**
     * Attribute: If accessible from HTTP only (forbids Javascript)
     */
    public const ATTR_HTTP_ONLY = 'httponly';
    /**
     * Attribute: If accessible from secured connection (HTTPS) only
     */
    public const ATTR_SECURE = 'secure';
    /**
     * Attribute: Control if cookie is sent with cross site requests
     */
    public const ATTR_SAME_SITE = 'samesite';


    /**
     * @var string The corresponding cookie name
     */
    public readonly string $name;
    /**
     * @var string The corresponding cookie value
     */
    public readonly string $value;


    /**
     * Constructor
     * @param string $name
     * @param string $value
     * @param array $arr
     * @param string|null $prefix
     */
    protected function __construct(string $name, string $value, array $arr, ?string $prefix = null)
    {
        parent::__construct($arr, $prefix);

        $this->name = $name;
        $this->value = $value;
    }


    /**
     * Calculate the effective expiry time of the cookie
     * @param CarbonInterface|null $received When the cookie was received
     * @return CarbonInterface|Invalid|null The calculated expiry time, or Invalid if the cookie expires immediately, or null if no expiry
     * @throws ArgumentException
     */
    public function calculateExpiry(?CarbonInterface $received = null) : CarbonInterface|Invalid|null
    {
        $received = $received ?? Carbon::now();

        $maxAge = $this->getMaxAge();
        if ($maxAge !== null) {
            if ($maxAge <= 0) return Invalid::instance();
            return CarbonImmutable::createFromTimestamp($received->getTimestamp() + $maxAge);
        }

        return $this->getExpires();
    }


    /**
     * The 'Expires' attribute
     * @return CarbonInterface|null
     * @throws ArgumentException
     */
    public function getExpires() : ?CarbonInterface
    {
        return $this->optional(static::ATTR_EXPIRES, HttpDateTimeParser::create());
    }


    /**
     * The 'Max-Age' attribute
     * @return int|null
     * @throws ArgumentException
     */
    public function getMaxAge() : ?int
    {
        return $this->optional(static::ATTR_MAX_AGE, IntegerParser::create());
    }


    /**
     * The 'Domain' attribute
     * @return string|null
     * @throws ArgumentException
     */
    public function getDomain() : ?string
    {
        return $this->optional(static::ATTR_DOMAIN, StringParser::create());
    }


    /**
     * The 'Path' attribute
     * @return string|null
     * @throws ArgumentException
     */
    public function getPath() : ?string
    {
        return $this->optional(static::ATTR_PATH, StringParser::create());
    }


    /**
     * If 'HttpOnly' attribute is set
     * @return bool
     * @throws ArgumentException
     */
    public function isHttpOnly() : bool
    {
        return $this->optional(static::ATTR_HTTP_ONLY, BooleanParser::create()) ?? false;
    }


    /**
     * If 'Secure' attribute is set
     * @return bool
     * @throws ArgumentException
     */
    public function isSecure() : bool
    {
        return $this->optional(static::ATTR_SECURE, BooleanParser::create()) ?? false;
    }


    /**
     * The 'SameSite' attribute
     * @return CookieSameSiteAttribute|null
     * @throws ArgumentException
     */
    public function getSameSite() : ?CookieSameSiteAttribute
    {
        $parser = ClosureParser::create(function (mixed $value, ?string $hintName) : CookieSameSiteAttribute {
            $value = StringParser::createTrimEmptyAsNull()->parse($value, $hintName);
            return match (strtolower($value)) {
                'strict' => CookieSameSiteAttribute::STRICT,
                'lax' => CookieSameSiteAttribute::LAX,
                'none' => CookieSameSiteAttribute::NONE,
                default => throw new UnsupportedValueException($value),
            };
        });

        return $this->optional(static::ATTR_SAME_SITE, $parser);
    }


    /**
     * @inheritDoc
     */
    protected function acceptKey(int|string $key) : string|int
    {
        if (is_string($key)) return strtolower($key);

        return parent::acceptKey($key);
    }


    /**
     * @inheritDoc
     */
    protected function formatKey(int|string $key) : string|int
    {
        if (is_string($key)) return strtolower($key);

        return parent::formatKey($key);
    }


    /**
     * @inheritDoc
     */
    public static function createParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : static {
            $value = StringParser::create()->parse($value, $hintName);

            $values = static::explodeValues($value, $keyValue);
            if ($keyValue === null || count($keyValue) !== 2) throw new InvalidDataException();

            return new static($keyValue[0], $keyValue[1], $values);
        });
    }


    /**
     * Explode the values
     * @param string $line
     * @param array|null $outKeyValue
     * @return array<string, string>
     * @throws SafetyCommonException
     */
    private static function explodeValues(string $line, ?array &$outKeyValue = null) : array
    {
        $ret = [];

        foreach (explode(';', $line) as $keyValue) {
            if (trim($keyValue) === '') continue;

            $equalPos = strpos($keyValue, '=');
            if ($equalPos === false) {
                // No equal sign, this is key attribute with no payload
                if ($outKeyValue === null) throw new InvalidDataException();

                $ret[static::decodeKey($keyValue)] = true;
            } else {
                // With equal sign, normal key value
                $key = substr($keyValue, 0, $equalPos);
                $value = substr($keyValue, $equalPos + 1);

                if ($outKeyValue === null) {
                    $outKeyValue = [ static::decodeKey($key, true), static::decodeValue($value) ];
                } else {
                    $ret[static::decodeKey($key)] = static::decodeValue($value);
                }
            }
        }

        return $ret;
    }


    /**
     * Decode a key
     * @param string $key
     * @param bool $isCaseSensitive
     * @return string
     */
    protected static function decodeKey(string $key, bool $isCaseSensitive = false) : string
    {
        $ret = trim($key);
        if (!$isCaseSensitive) $ret = strtolower($ret);
        return $ret;
    }


    /**
     * Decode a value
     * @param string $value
     * @return string
     */
    protected static function decodeValue(string $value) : string
    {
        $ret = trim($value);
        if (str_starts_with($ret, '"') && str_ends_with($ret, '"')) {
            $ret = substr($ret, 1, -1);
        }
        return rawurldecode($ret);
    }
}