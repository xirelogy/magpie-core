<?php

namespace Magpie\Objects;

use Exception;
use Magpie\Codecs\Concepts\PreferStringable;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\SafetyCommonException;

/**
 * Representation of a URI
 */
abstract class CommonUri implements PreferStringable
{
    /**
     * @var string|null URI scheme
     */
    public ?string $scheme = null;
    /**
     * @var string|null Username specification
     */
    public ?string $username = null;
    /**
     * @var string|null Password specification
     */
    public ?string $password = null;
    /**
     * @var string|null Hostname
     */
    public ?string $host = null;
    /**
     * @var int|null Specific port number
     */
    public ?int $port = null;
    /**
     * @var string Path
     */
    public string $path;
    /**
     * @var string|null Fragment string (after the '#' mark)
     */
    public ?string $fragment = null;


    /**
     * Constructor
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }


    /**
     * Query string
     * @return string|null
     */
    public abstract function getQueryString() : ?string;


    /**
     * Query string
     * @param string|null $query
     * @return void
     */
    protected abstract function setQueryString(?string $query) : void;


    /**
     * @inheritDoc
     */
    public function __toString() : string
    {
        $ret = '';

        if ($this->host !== null) {
            $ret .= $this->scheme !== null ? ($this->scheme . '://') : '//';

            if ($this->username !== null) {
                $ret .= $this->username;
                if ($this->password !== null) {
                    $ret .= ':' . $this->password;
                }
                $ret .= '@';
            }

            $ret .= $this->host;

            if ($this->port !== null) {
                $ret .= ':' . $this->port;
            }
        }

        $ret .= $this->path;

        $query = $this->getQueryString();
        if ($query !== null) {
            $ret .= '?' . $query;
        }

        if ($this->fragment !== null) {
            $ret .= '#' . $this->fragment;
        }

        return $ret;
    }


    /**
     * Parse from given URI text
     * @param string $uri
     * @return static
     * @throws SafetyCommonException
     */
    public static function parse(string $uri) : static
    {
        $components = parse_url($uri);
        if ($components === false) throw new InvalidDataException();

        $path = $components['path'] ?? '/';
        $ret = new static($path);

        $ret->scheme = $components['scheme'] ?? null;
        $ret->username = $components['user'] ?? null;
        $ret->password = $components['pass'] ?? null;
        $ret->host = $components['host'] ?? null;
        $ret->port = array_key_exists('port', $components) ? intval($components['port']) : null;

        $ret->setQueryString($components['query'] ?? null);
        $ret->fragment = $components['fragment'] ?? null;

        return $ret;
    }


    /**
     * Safe parse from given URI text
     * @param string $uri
     * @param string $defaultPath
     * @return static
     */
    public static function safeParse(string $uri, string $defaultPath = '/') : static
    {
        try {
            return static::parse($uri);
        } catch (Exception) {
            return new static($defaultPath);
        }
    }


    /**
     * Copy/duplicate from source to target
     * @param CommonUri $source
     * @param CommonUri $target
     * @return void
     */
    protected static function copy(CommonUri $source, CommonUri $target) : void
    {
        $target->scheme = $source->scheme;
        $target->username = $source->username;
        $target->password = $source->password;
        $target->host = $source->host;
        $target->path = $source->path;
        $target->setQueryString($source->getQueryString());
        $target->fragment = $source->fragment;
    }
}