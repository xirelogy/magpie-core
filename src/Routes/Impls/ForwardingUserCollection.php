<?php

namespace Magpie\Routes\Impls;

use Magpie\Codecs\Parsers\Parser;
use Magpie\HttpServer\Concepts\UserCollectable;
use Magpie\HttpServer\Collection;
use Magpie\Locales\Concepts\Localizable;

/**
 * An actual user collection that is forwarded
 * Target: domain arguments, route arguments
 * @internal
 */
final class ForwardingUserCollection implements UserCollectable
{
    /**
     * @var UserCollectable Forwarded user collection
     */
    protected UserCollectable $base;
    /**
     * @var string|Localizable|null Current argument type
     */
    protected readonly string|Localizable|null $argType;


    /**
     * Constructor
     */
    public function __construct(string|Localizable|null $argType)
    {
        $this->argType = $argType;
        $this->base = static::createBaseCollection([], $this->argType);
    }


    /**
     * @inheritDoc
     */
    public function has(string|int $key) : bool
    {
        return $this->base->has($key);
    }


    /**
     * @inheritDoc
     */
    public function requires(string|int $key, ?Parser $parser = null) : mixed
    {
        return $this->base->requires($key, $parser);
    }


    /**
     * @inheritDoc
     */
    public function optional(string|int $key, ?Parser $parser = null, mixed $default = null) : mixed
    {
        return $this->base->optional($key, $parser, $default);
    }


    /**
     * @inheritDoc
     */
    public function safeOptional(string|int $key, ?Parser $parser = null, mixed $default = null) : mixed
    {
        return $this->base->safeOptional($key, $parser, $default);
    }


    /**
     * @inheritDoc
     */
    public function fullKey(string|int $key) : string
    {
        return $this->base->fullKey($key);
    }


    /**
     * @inheritDoc
     */
    public function getNextPrefix(string|int|null $key) : ?string
    {
        return $this->base->getNextPrefix($key);
    }


    /**
     * @inheritDoc
     */
    public function getKeys() : iterable
    {
        return $this->base->getKeys();
    }


    /**
     * @inheritDoc
     */
    public function all() : iterable
    {
        return $this->base->all();
    }


    /**
     * Reconfigured the values
     * @param iterable<string, mixed> $vars
     * @return void
     * @internal
     */
    public function _reconfigure(iterable $vars) : void
    {
        $this->base = static::createBaseCollection($vars, $this->argType);
    }


    /**
     * Create a base collection
     * @param iterable<string, mixed> $vars
     * @param string|Localizable|null $argType
     * @return UserCollectable
     */
    private static function createBaseCollection(iterable $vars, string|Localizable|null $argType) : UserCollectable
    {
        return new class(iter_flatten($vars), $argType) extends Collection implements UserCollectable {
            /**
             * Constructor
             * @param array<string, mixed> $keyValues
             * @param string|Localizable|null $argType
             */
            public function __construct(array $keyValues, string|Localizable|null $argType)
            {
                parent::__construct($keyValues, null, $argType);
            }


            /**
             * @inheritDoc
             */
            public function fullKey(int|string $key) : string
            {
                $prefix = !is_empty_string($this->prefix) ? ($this->prefix . '.') : '';
                return $prefix . ":$key";
            }
        };
    }
}