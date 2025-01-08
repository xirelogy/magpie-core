<?php

namespace Magpie\Facades\Redis;

use Exception;
use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\Parser;
use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\LogContainable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Factories\ClassFactory;
use Magpie\System\Concepts\SystemBootable;

/**
 * A redis client
 */
abstract class RedisClient implements TypeClassable, LogContainable, SystemBootable
{
    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * Check if given key exist
     * @param string $key
     * @return bool
     * @throws Exception
     */
    public abstract function has(string $key) : bool;


    /**
     * Get value from specific key
     * @param string $key Key
     * @return string|null
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function get(string $key) : ?string;


    /**
     * Get multiple values from specific keys
     * @param iterable<string> $keys
     * @return iterable<string, string|null>
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function getMultiple(iterable $keys) : iterable;


    /**
     * Set value for specific key
     * @param string $key Key
     * @param string $value Value to be stored
     * @param int|Duration|null $ttl Time-to-live of the key-value with reference to now, if specified
     * @return bool
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function set(string $key, string $value, int|Duration|null $ttl = null) : bool
    {
        return $this->setWithOptions($key, $value)
            ->withTtl($ttl)
            ->go();
    }


    /**
     * Set value for specific key, with specific options
     * @param string $key
     * @param string $value
     * @return RedisSetCommand
     */
    public abstract function setWithOptions(string $key, string $value) : RedisSetCommand;


    /**
     * Set or update the time-to-life of given key
     * @param string $key
     * @param int|Duration $ttl
     * @return bool
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function setTtl(string $key, int|Duration $ttl) : bool;


    /**
     * Delete a key
     * @param string $key
     * @return bool
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function delete(string $key) : bool;


    /**
     * Clears everything
     * @return bool
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function clear() : bool;


    /**
     * Get list size
     * @param string $key Key of the list
     * @return int
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function listSize(string $key) : int;


    /**
     * Push element(s) to the front of the list
     * @param string $key Key of the list
     * @param string ...$values Values to be pushed into the list
     * @return bool
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function listPushFront(string $key, string ...$values) : bool;


    /**
     * Pop (return and remove) the first element in the list
     * @param string $key Key of the list
     * @param RedisBlockingOptions|null $blocking Blocking options, if the operation shall be blocking until item available
     * @return string|null
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function listPopFront(string $key, ?RedisBlockingOptions $blocking = null) : ?string;


    /**
     * Push element(s) to the back of the list
     * @param string $key Key of the list
     * @param string ...$values Values to be pushed into the list
     * @return bool
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function listPushBack(string $key, string ...$values) : bool;


    /**
     * Pop (return and remove) the last element in the list
     * @param string $key Key of the list
     * @param RedisBlockingOptions|null $blocking Blocking options, if the operation shall be blocking until item available
     * @return string|null
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function listPopBack(string $key, ?RedisBlockingOptions $blocking = null) : ?string;


    /**
     * Get sorted set size (cardinal size)
     * @param string $key Key of the sorted list
     * @return int
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function sortedSetSize(string $key) : int;


    /**
     * Add value to sorted set identified by specific key, with specific score and value
     * @param string $key Key of the sorted list
     * @param float $score Score attached to the value
     * @param mixed $value Value to be added
     * @return int
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function sortedSetPush(string $key, float $score, mixed $value) : int
    {
        return $this->sortedSetPushWithOptions($key)
            ->add($score, $value)
            ->go();
    }


    /**
     * Add values to sorted set identified by specific key, with specific options
     * @param string $key Key of the sorted list
     * @return RedisSortedSetPushCommand
     */
    public abstract function sortedSetPushWithOptions(string $key) : RedisSortedSetPushCommand;


    /**
     * Get values from sorted set with specific options
     * @param string $key Key of the sorted list
     * @return RedisSortedSetGetCommand
     */
    public abstract function sortedSetGetWithOptions(string $key) : RedisSortedSetGetCommand;


    /**
     * Delete value from sorted set
     * @param string $key Key of the sorted list
     * @param mixed $value Value to be deleted
     * @return int Number of values deleted
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function sortedSetDelete(string $key, mixed $value) : int;


    /**
     * Evaluate a LUA script
     * @param RedisLuaScript $script LUA script to be executed
     * @param mixed ...$arguments Arguments to the script
     * @return mixed
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function eval(RedisLuaScript $script, mixed ...$arguments) : mixed;


    /**
     * Initialize a client
     * @param RedisClientConfig $config
     * @param string|null $typeClass
     * @return static
     * @throws SafetyCommonException
     */
    public static function initialize(RedisClientConfig $config, ?string $typeClass = null) : static
    {
        $className = ClassFactory::resolve($typeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificInitialize($config);
    }


    /**
     * Initialize a client specifically for this type of adaptation
     * @param RedisClientConfig $config
     * @return static
     * @throws SafetyCommonException
     */
    protected abstract static function specificInitialize(RedisClientConfig $config) : static;


    /**
     * Create redis equivalent key
     * @param string $namespace
     * @param string $key
     * @return string
     */
    public static function makeRedisKey(string $namespace, string $key) : string
    {
        return "::$namespace:$key";
    }


    /**
     * Create a parser to parse redis client from environment
     * @return Parser<static>
     */
    public static function createEnvParser() : Parser
    {
        return ClosureParser::create(function (mixed $value, ?string $hintName) : static {
            $redisConfig = RedisClientConfig::createEnvParser()->parse($value, $hintName);
            return RedisClient::initialize($redisConfig);
        });
    }
}