<?php

namespace Magpie\Routes\Impls;

use Magpie\Codecs\Parsers\Parser;
use Magpie\HttpServer\Collection;
use Magpie\HttpServer\Concepts\UserCollectable;

/**
 * An actual user collection that is forwarded
 * @internal
 */
final class ForwardingUserCollection implements UserCollectable
{
    /**
     * @var UserCollectable Forwarded user collection
     */
    protected UserCollectable $base;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->base = static::createBaseCollection([]);
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
        $this->base = static::createBaseCollection($vars);
    }


    /**
     * Create a base collection
     * @param iterable<string, mixed> $vars
     * @return UserCollectable
     */
    private static function createBaseCollection(iterable $vars) : UserCollectable
    {
        return new class(iter_flatten($vars)) extends Collection implements UserCollectable {
            /**
             * Constructor
             * @param array<string, mixed> $keyValues
             */
            public function __construct(array $keyValues)
            {
                parent::__construct($keyValues);
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