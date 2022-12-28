<?php

namespace Magpie\Queues\Providers\Redis\Impls;

use Magpie\Facades\Redis\RedisLuaScript;
use Magpie\General\Traits\StaticClass;

/**
 * LUA script for queue implementation using Redis
 * @note: Heavily referenced from Laravel's queue implementation (Illuminate\Queue\LuaScripts)
 * @internal
 */
class RedisQueueScript
{
    use StaticClass;


    /**
     * LUA script: push a job to the queue
     * @return RedisLuaScript
     */
    public static function push() : RedisLuaScript
    {
        return RedisLuaScript::create(<<<'LUA'
-- Push the job onto the queue...
redis.call('rpush', KEYS[1], ARGV[1])
-- Push a notification onto the "notify" queue...
redis.call('rpush', KEYS[2], 1)
LUA, 2);
    }


    /**
     * LUA script: pop job from the queue
     * @return RedisLuaScript
     */
    public static function pop() : RedisLuaScript
    {
        return RedisLuaScript::create(<<<'LUA'
-- Pop the first job off of the queue...
local job = redis.call('lpop', KEYS[1])
local reserved = false

if(job ~= false) then
    -- Increment the attempt count and place job on the reserved queue...
    reserved = cjson.decode(job)
    reserved['attempts'] = reserved['attempts'] + 1
    reserved = cjson.encode(reserved)
    redis.call('zadd', KEYS[2], ARGV[1], reserved)
    redis.call('lpop', KEYS[3])
end

return {job, reserved}
LUA, 3);
    }


    /**
     * LUA script: migrate matured jobs to the main queue
     * @return RedisLuaScript
     */
    public static function migrateMatured() : RedisLuaScript
    {
        return RedisLuaScript::create(<<<'LUA'
-- Get all of the jobs with an expired "score"...
local val = redis.call('zrangebyscore', KEYS[1], '-inf', ARGV[1])

-- If we have values in the array, we will remove them from the first queue
-- and add them onto the destination queue in chunks of 100, which moves
-- all of the appropriate jobs onto the destination queue very safely.
if(next(val) ~= nil) then
    redis.call('zremrangebyrank', KEYS[1], 0, #val - 1)

    for i = 1, #val, 100 do
        redis.call('rpush', KEYS[2], unpack(val, i, math.min(i+99, #val)))
        -- Push a notification for every job that was migrated...
        for j = i, math.min(i+99, #val) do
            redis.call('rpush', KEYS[3], 1)
        end
    end
end

return val
LUA, 3);
    }


    /**
     * LUA script: repost reserved job
     * @return RedisLuaScript
     */
    public static function repostReserved() : RedisLuaScript
    {
        return RedisLuaScript::create(<<<'LUA'
-- Remove the job from the current queue...
redis.call('zrem', KEYS[2], ARGV[1])

-- Add the job onto the "delayed" queue...
redis.call('zadd', KEYS[1], ARGV[2], ARGV[1])

return true
LUA, 2);
    }
}