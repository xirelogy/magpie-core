<?php

/** @noinspection PhpRedundantCatchClauseInspection */

namespace Magpie\Facades\Redis\PhpRedis;

use Exception;
use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\MissingArgumentException;
use Magpie\Exceptions\UnsupportedException;
use Magpie\Exceptions\UnsupportedValueException;
use Magpie\Facades\Redis\Exceptions\RedisOperationFailedException;
use Magpie\Facades\Redis\Exceptions\RedisSafetyException;
use Magpie\Facades\Redis\Exceptions\RedisSelectDatabaseFailedException;
use Magpie\Facades\Redis\PhpRedis\Exceptions\PhpRedisClientException;
use Magpie\Facades\Redis\RedisBlockingOptions;
use Magpie\Facades\Redis\RedisClient;
use Magpie\Facades\Redis\RedisClientConfig;
use Magpie\Facades\Redis\RedisLuaScript;
use Magpie\Facades\Redis\RedisSetCommand;
use Magpie\Facades\Redis\RedisSortedSetGetCommand;
use Magpie\Facades\Redis\RedisSortedSetPushCommand;
use Magpie\Facades\Redis\RedisSortOrder;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\General\MultiPrecision;
use Magpie\Logs\Concepts\Loggable;
use Magpie\Objects\BasicUsernamePassword;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;
use Redis;
use RedisException as PhpRedisException;

/**
 * Redis client utilizing 'phpredis' implementation
 */
#[FactoryTypeClass(PhpRedisClient::TYPECLASS, RedisClient::class)]
class PhpRedisClient extends RedisClient
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'phpredis';

    /**
     * @var Redis The underlying redis connection object
     */
    protected Redis $redis;


    /**
     * Constructor
     * @param Redis $redis
     */
    protected function __construct(Redis $redis)
    {
        parent::__construct();

        $this->redis = $redis;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    public function setLogger(Loggable $logger) : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function has(string $key) : bool
    {
        return static::safeExecute('exists', fn () => $this->redis->exists($key));
    }


    /**
     * @inheritDoc
     */
    public function get(string $key) : ?string
    {
        $ret = static::safeExecute('get', fn () => $this->redis->get($key));

        return $ret !== false ? "$ret" : null;
    }


    /**
     * @inheritDoc
     */
    public function getMultiple(iterable $keys) : iterable
    {
        $keys = iter_flatten($keys, false);

        $values = static::safeExecute('mGet', fn () => $this->redis->mGet($keys));

        $i = 0;
        foreach ($values as $value) {
            $value = $value !== false ? "$value" : null;
            yield $keys[$i] => $value;
            ++$i;
        }
    }


    /**
     * @inheritDoc
     */
    public function setWithOptions(string $key, string $value) : RedisSetCommand
    {
        return new class($this->redis, $key, $value) extends RedisSetCommand {
            /**
             * Constructor
             * @param Redis $redis
             * @param string $key
             * @param string $value
             */
            public function __construct(
                protected Redis $redis,
                protected string $key,
                protected string $value,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function go() : bool
            {
                $options = [];

                if ($this->ifNotYetExist) {
                    $options[] = 'nx';
                } else if ($this->ifAlreadyExist) {
                    $options[] = 'xx';
                }

                if ($this->ttl !== null) {
                    $matchedValue = MultiPrecision::matchPrecision($this->ttl, [-3, 0], $matchedPrecision);

                    switch ($matchedPrecision) {
                        case -3:
                            $options['px'] = $matchedValue;
                            break;
                        case 0:
                            $options['ex'] = $matchedValue;
                            break;
                    }
                }

                try {
                    if (count($options) > 0) {
                        return $this->redis->set($this->key, $this->value, $options) !== false;
                    } else {
                        return $this->redis->set($this->key, $this->value) !== false;
                    }
                } catch (RedisSafetyException $ex) {
                    throw $ex;
                } catch (PhpRedisException $ex) {
                    throw new PhpRedisClientException($ex->getMessage(), 'set');
                } catch (Exception $ex) {
                    throw new RedisOperationFailedException(previous: $ex);
                }
            }
        };
    }


    /**
     * @inheritDoc
     */
    public function setTtl(string $key, int|Duration $ttl) : bool
    {
        $ttl = Duration::accept($ttl);
        $matchedValue = MultiPrecision::matchPrecision($ttl, [-3, 0], $matchedPrecision);

        return match ($matchedPrecision) {
            -3 => static::safeExecute('pExpire', fn () => $this->redis->pExpire($key, $matchedValue) !== false),
            0 => static::safeExecute('expire', fn () => $this->redis->expire($key, $matchedValue) !== false),
            default => throw new UnsupportedException(),
        };
    }


    /**
     * @inheritDoc
     */
    public function delete(string $key) : bool
    {
        return static::safeExecute('del', fn () => $this->redis->del($key) > 0);
    }


    /**
     * @inheritDoc
     */
    public function listSize(string $key) : int
    {
        $ret = static::safeExecute('lLen', fn () => $this->redis->lLen($key));
        if ($ret === false) throw new InvalidDataException();

        return $ret;
    }


    /**
     * @inheritDoc
     */
    public function listPushFront(string $key, string ...$values) : bool
    {
        return static::safeExecute('lPush', fn () => $this->redis->lPush($key, ...$values) !== false);
    }


    /**
     * @inheritDoc
     */
    public function listPopFront(string $key, ?RedisBlockingOptions $blocking = null) : ?string
    {
        return static::safeExecute('lPop', function() use($key, $blocking) {
            if ($blocking !== null) {
                $ret = $this->redis->blPop($key, $blocking->timeout->getSeconds());
                return static::bPopResult($ret);
            } else {
                $ret = $this->redis->lPop($key);
                return static::popResult($ret);
            }
        });
    }


    /**
     * @inheritDoc
     */
    public function listPushBack(string $key, string ...$values) : bool
    {
        return static::safeExecute('rPush', fn () => $this->redis->rPush($key, ...$values) !== false);
    }


    /**
     * @inheritDoc
     */
    public function listPopBack(string $key, ?RedisBlockingOptions $blocking = null) : ?string
    {
        return static::safeExecute('rPop', function() use($key, $blocking) {
            if ($blocking !== null) {
                $ret = $this->redis->brPop($key, $blocking->timeout->getSeconds());
                return static::bPopResult($ret);
            } else {
                $ret = $this->redis->rPop($key);
                return static::popResult($ret);
            }
        });
    }


    /**
     * Extract result from blocking list operation (blPop/brPop)
     * @param array $ret
     * @return string|null
     */
    protected static function bPopResult(array $ret) : ?string
    {
        if (count($ret) <= 0) return null;
        return $ret[0];
    }


    /**
     * Extract result from list operation (lPop/rPop)
     * @param string|bool $ret
     * @return string|null
     */
    protected static function popResult(string|bool $ret) : ?string
    {
        if ($ret === false) return null;
        return $ret;
    }


    /**
     * @inheritDoc
     */
    public function sortedSetSize(string $key) : int
    {
        return static::safeExecute('zCard()', fn() => $this->redis->zCard($key));
    }


    /**
     * @inheritDoc
     */
    public function sortedSetPushWithOptions(string $key) : RedisSortedSetPushCommand
    {
        return new class($this->redis, $key) extends RedisSortedSetPushCommand {
            /**
             * Constructor
             * @param Redis $redis
             * @param string $key
             */
            public function __construct(
                protected Redis $redis,
                protected string $key,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function go() : int
            {
                $options = [];

                if ($this->ifNotYetExist) {
                    $options[] = 'nx';
                } else if ($this->ifAlreadyExist) {
                    $options[] = 'xx';
                }

                $packedValues = [];

                $totalScores = count($this->scores);
                $totalValues = count($this->values);
                if ($totalScores !== $totalValues) throw new InvalidDataException();

                for ($i = 0; $i < $totalScores; ++$i) {
                    $packedValues[] = $this->scores[$i];
                    $packedValues[] = $this->values[$i];
                }

                if (count($packedValues) <= 0) throw new MissingArgumentException();

                try {
                    return $this->redis->zAdd($this->key, $options, ...$packedValues);
                } catch (RedisSafetyException $ex) {
                    throw $ex;
                } catch (PhpRedisException $ex) {
                    throw new PhpRedisClientException($ex->getMessage(), 'zAdd');
                } catch (Exception $ex) {
                    throw new RedisOperationFailedException(previous: $ex);
                }
            }
        };
    }


    /**
     * @inheritDoc
     */
    public function sortedSetGetWithOptions(string $key) : RedisSortedSetGetCommand
    {
        return new class($this->redis, $key) extends RedisSortedSetGetCommand {
            /**
             * Constructor
             * @param Redis $redis
             * @param string $key
             */
            public function __construct(
                protected Redis $redis,
                protected string $key,
            ) {

            }


            /**
             * @inheritDoc
             */
            public function query() : iterable
            {
                $min = static::translateScore($this->minScore, $this->isMinInclusive, '-inf');
                $max = static::translateScore($this->maxScore, $this->isMaxInclusive, '+inf');

                $options = [
                    'withscores' => true,
                ];

                try {
                    $result = match ($this->order) {
                        RedisSortOrder::ASC => $this->redis->zRangeByScore($this->key, $min, $max, $options),
                        RedisSortOrder::DESC => $this->redis->zRevRangeByScore($this->key, $max, $min, $options),
                        default => throw new UnsupportedValueException($this->order, _l('sort')),
                    };
                } catch (RedisSafetyException $ex) {
                    throw $ex;
                } catch (PhpRedisException $ex) {
                    throw new PhpRedisClientException($ex->getMessage(), 'zRangeByScore');
                } catch (Exception $ex) {
                    throw new RedisOperationFailedException(previous: $ex);
                }

                foreach ($result as $value => $score) {
                    yield $score => $value;
                }
            }


            /**
             * Translate score
             * @param float|null $score
             * @param bool $isInclusive
             * @param string $defaultScore
             * @return string
             */
            protected static function translateScore(?float $score, bool $isInclusive, string $defaultScore) : string
            {
                if ($score === null) return $defaultScore;

                if ($isInclusive) {
                    return "$score";
                } else {
                    return "($score";
                }
            }
        };
    }


    /**
     * @inheritDoc
     */
    public function sortedSetDelete(string $key, mixed $value) : int
    {
        return static::safeExecute('zRem()', fn() => $this->redis->zRem($key, $value));
    }


    /**
     * @inheritDoc
     */
    public function eval(RedisLuaScript $script, mixed ...$arguments) : mixed
    {
        return static::safeExecute('eval()', function() use($script, $arguments) {
            $this->redis->clearLastError();
            $ret = $this->redis->eval($script->content, $arguments, $script->numberOfKeys);

            $err = $this->redis->getLastError();
            if ($err !== null) throw new RedisOperationFailedException($err);

            return $ret;
        });
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitialize(RedisClientConfig $config) : static
    {
        return static::safeExecute(_l('initialize'), function() use($config) {
            $redis = new Redis();

            $redis->connect($config->host, $config->port ?? RedisClientConfig::DEFAULT_PORT);

            if ($config->auth instanceof BasicUsernamePassword) {
                // Authenticate using username/password
                $redis->auth([$config->auth->username, $config->auth->password]);
            } else if (is_string($config->auth)) {
                // Authenticate using password only
                $redis->auth($config->auth);
            }

            if ($config->database !== null) {
                if (!$redis->select($config->database)) throw new RedisSelectDatabaseFailedException();
            }

            return new static($redis);
        });
    }


    /**
     * Safe execute
     * @param string $purpose
     * @param callable():mixed $fn
     * @return mixed
     * @throws RedisSafetyException
     */
    protected static function safeExecute(string $purpose, callable $fn) : mixed
    {
        try {
            return $fn();
        } catch (RedisSafetyException $ex) {
            throw $ex;
        } catch (PhpRedisException $ex) {
            throw new PhpRedisClientException($ex->getMessage(), $purpose);
        } catch (Exception $ex) {
            throw new RedisOperationFailedException(previous: $ex);
        }
    }


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        $registrar
            ->provides(RedisClient::class)
            ;

        return true;
    }


    /**
     * @inheritDoc
     */
    public static function systemBoot(BootContext $context) : void
    {
        ClassFactory::includeDirectory(__DIR__);

        ClassFactory::setDefaultTypeClassCheck(RedisClient::class, function () : ?string {
            if (!extension_loaded('redis')) return null;
            return static::TYPECLASS;
        });
    }
}