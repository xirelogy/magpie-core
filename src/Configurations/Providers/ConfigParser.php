<?php

namespace Magpie\Configurations\Providers;

use Magpie\Configurations\ConfigKey;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\General\Concepts\TypeClassable;

/**
 * Configuration parser instance
 */
abstract class ConfigParser implements TypeClassable
{
    /**
     * @var ConfigProvider Provider instance
     */
    public readonly ConfigProvider $provider;
    /**
     * @var array<string, ConfigKey> Keys supported by current provider
     */
    protected array $keys;
    /**
     * @var array<string, mixed> Associated context
     */
    protected readonly array $contexts;


    /**
     * Constructor
     * @param ConfigProvider $provider
     * @param iterable<string|null, ConfigKey> $keys
     * @param array<string, mixed> $contexts
     */
    public function __construct(ConfigProvider $provider, iterable $keys, array $contexts)
    {
        $this->provider = $provider;
        $this->keys = iter_flatten(static::acceptKeys($keys));
        $this->contexts = $contexts;
    }


    /**
     * Check if value exist for current configuration
     * @param ConfigKey|string $key
     * @return bool
     */
    public final function has(ConfigKey|string $key) : bool
    {
        if (is_string($key)) {
            if (!array_key_exists($key, $this->keys)) return false;
            $key = $this->keys[$key];
        }

        return $this->onHasConfig($key);
    }


    /**
     * Check if value exist for current configuration
     * @param ConfigKey $key
     * @return bool
     */
    protected abstract function onHasConfig(ConfigKey $key) : bool;


    /**
     * Get value for current configuration
     * @param ConfigKey<T>|string $key
     * @return T|null
     * @throws ArgumentException
     * @template T
     */
    public final function get(ConfigKey|string $key) : mixed
    {
        if (is_string($key)) {
            if (!array_key_exists($key, $this->keys)) throw new MissingArgumentException($key);
            $key = $this->keys[$key];
        }

        return $this->onGetConfig($key);
    }


    /**
     * Get value for current configuration
     * @param ConfigKey<T> $key
     * @return T|null
     * @throws ArgumentException
     * @template T
     */
    protected abstract function onGetConfig(ConfigKey $key) : mixed;


    /**
     * Add supported keys
     * @param iterable<string|null, ConfigKey> $keys
     * @return void
     */
    public final function addKeys(iterable $keys) : void
    {
        foreach (static::acceptKeys($keys) as $keyName => $keyValue) {
            $this->keys[$keyName] = $keyValue;
        }
    }


    /**
     * Try to get context
     * @param string $key
     * @param mixed|null $defaultValue
     * @return mixed|null
     */
    public final function getContext(string $key, mixed $defaultValue = null) : mixed
    {
        return $this->contexts[$key] ?? $defaultValue;
    }


    /**
     * Translate keys
     * @param iterable<string|null, ConfigKey> $keys
     * @return iterable<string, ConfigKey>
     */
    protected static function acceptKeys(iterable $keys) : iterable
    {
        foreach ($keys as $keyName => $keyValue) {
            $keyName = $keyName ?? $keyValue->name->getKey();
            yield $keyName => $keyValue;
        }
    }
}