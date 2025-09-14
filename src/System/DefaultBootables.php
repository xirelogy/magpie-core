<?php

namespace Magpie\System;

use Magpie\Caches\Providers\RedisCacheConfig;
use Magpie\Cryptos\Providers\OpenSsl\SpecContext as OpenSslSpecContext;
use Magpie\Facades\FileSystem\Providers\Local\LocalFileSystem;
use Magpie\Facades\Http\Curl\CurlHttpClient;
use Magpie\Facades\Mutex\Providers\RedisMutexConfig;
use Magpie\Facades\Redis\PhpRedis\PhpRedisClient;
use Magpie\General\Traits\StaticClass;
use Magpie\Models\Providers\Mysql\MysqlConnection;
use Magpie\Models\Providers\Pgsql\PgsqlConnection;
use Magpie\Models\Providers\Sqlite\SqliteConnection;
use Magpie\Queues\Providers\Redis\RedisQueueCreator;
use Magpie\Schedules\Impls\ScheduleRegistry;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Kernel\CoreFeatures;

/**
 * Declaration of all bootables
 */
class DefaultBootables
{
    use StaticClass;


    /**
     * All system-default bootable classes
     * @return iterable<class-string<SystemBootable>>
     */
    public static function getClasses() : iterable
    {
        yield CoreFeatures::class;
        yield LocalFileSystem::class;
        yield ScheduleRegistry::class;
        yield OpenSslSpecContext::class;
        yield MysqlConnection::class;
        yield PgsqlConnection::class;
        yield SqliteConnection::class;
        yield CurlHttpClient::class;
        yield PhpRedisClient::class;
        yield RedisCacheConfig::class;
        yield RedisMutexConfig::class;
        yield RedisQueueCreator::class;
    }
}