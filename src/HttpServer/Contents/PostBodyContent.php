<?php

namespace Magpie\HttpServer\Contents;

use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\General\Concepts\PrimitiveBinaryContentable;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\Names\CommonHttpHeader;
use Magpie\General\Str;
use Magpie\HttpServer\Concepts\PostBodyContentable;
use Magpie\HttpServer\HeaderCollection;
use Magpie\HttpServer\ServerCollection;

/**
 * Common POST body content
 */
abstract class PostBodyContent implements PostBodyContentable
{
    /**
     * @var ServerCollection Server variables
     */
    protected readonly ServerCollection $serverVars;
    /**
     * @var HeaderCollection HTTP Headers
     */
    protected readonly HeaderCollection $headers;
    /**
     * @var string POST body
     */
    protected readonly string $body;


    /**
     * Constructor
     * @param ServerCollection $serverVars
     * @param string $body
     */
    protected function __construct(ServerCollection $serverVars, string $body)
    {
        $this->serverVars = $serverVars;
        $this->headers = $serverVars->getHeaders();
        $this->body = $body;
    }


    /**
     * @inheritDoc
     */
    public final function getVariables() : iterable
    {
        $retVars = [];

        foreach ($this->onGetVariables() as $name => $payload) {
            $nameKey = PostKey::from($name);
            $finalIndices = array_merge([$nameKey->name], $nameKey->indices);
            $retVars = static::assignValue($retVars, $finalIndices, $payload);
        }

        yield from $retVars;
    }


    /**
     * Assign value to the variable host
     * @param mixed $host
     * @param array $indices
     * @param array|string|PrimitiveBinaryContentable $payload
     * @return array|string|PrimitiveBinaryContentable
     */
    private static function assignValue(mixed $host, array $indices, array|string|PrimitiveBinaryContentable $payload) : array|string|PrimitiveBinaryContentable
    {
        // Exit condition
        if (count($indices) <= 0) return $payload;

        // Extract current index
        $thisIndex = array_shift($indices);

        // Upgrade if necessary
        if (!is_array($host)) $host = [];

        // Defer to next level
        if ($thisIndex !== '') {
            if (is_numeric($thisIndex) && !str_contains($thisIndex, '.')) $thisIndex = intval($thisIndex);
            $nextHost = $host[$thisIndex] ?? null;
            $host[$thisIndex] = static::assignValue($nextHost, $indices, $payload);
        } else {
            $host[] = static::assignValue(null, $indices, $payload);
        }

        // And return
        return $host;
    }


    /**
     * Extract post variables from post content
     * @return iterable<string, string|PrimitiveBinaryContentable>
     */
    protected abstract function onGetVariables() : iterable;


    /**
     * Create from specific server variables and POST body
     * @param ServerCollection $serverVars
     * @param string $body
     * @return static|null
     */
    public static final function create(ServerCollection $serverVars, string $body) : ?static
    {
        $headers = $serverVars->getHeaders();
        $contentType = $headers->safeOptional(CommonHttpHeader::CONTENT_TYPE, static::createContentTypeParser());

        $className = ClassFactory::safeResolve($contentType, self::class);
        if ($className === null) return null;
        if (!is_subclass_of($className, self::class)) return null;

        return $className::onSpecificCreate($serverVars, $body);
    }


    /**
     * Create a content type parser
     * @return Parser
     */
    protected static function createContentTypeParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : ?string {
            $value = StringParser::create()->parse($value, $hintName);
            $components = explode(';', $value);
            $ret = trim($components[0]);
            if (Str::isNullOrEmpty($ret)) return null;

            return $ret;
        });
    }


    /**
     * Create specific instance
     * @param ServerCollection $serverVars
     * @param string $body
     * @return static
     */
    protected static function onSpecificCreate(ServerCollection $serverVars, string $body) : static
    {
        return new static($serverVars, $body);
    }
}