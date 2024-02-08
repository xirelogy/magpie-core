<?php

namespace Magpie\Queues\Providers\Redis;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Magpie\Codecs\ParserHosts\ObjectParserHost;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Redis\RedisBlockingOptions;
use Magpie\Facades\Redis\RedisClient;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Simples\SimpleJSON;
use Magpie\Queues\Concepts\Queueable;
use Magpie\Queues\Concepts\QueueRunnable;
use Magpie\Queues\PendingExecutable;
use Magpie\Queues\Providers\Queue;
use Magpie\Queues\Providers\Redis\Impls\RedisQueueEncodingKeys;
use Magpie\Queues\Providers\Redis\Impls\RedisPendingExecutable;
use Magpie\Queues\Providers\Redis\Impls\RedisQueueScript;
use Magpie\Queues\Simples\FailedExecutableEncoded;
use Magpie\Queues\Simples\QueueArguments;

/**
 * Queue implementation using Redis
 */
class RedisQueue extends Queue
{
    /**
     * Queue sub-tag: restart signal
     */
    protected const TAG_RESTART = 'restart';
    /**
     * Queue sub-tag: notify
     */
    protected const TAG_NOTIFY = 'notify';
    /**
     * Queue sub-tag: delayed
     */
    protected const TAG_DELAYED = 'delayed';
    /**
     * Queue sub-tag: reserved
     */
    protected const TAG_RESERVED = 'reserved';

    /**
     * @var RedisClient Redis client
     */
    protected readonly RedisClient $redis;


    /**
     * Constructor
     * @param RedisClient $redis
     * @param string|null $name
     * @param Duration|null $retryTimeout
     */
    public function __construct(RedisClient $redis, ?string $name = null, ?Duration $retryTimeout = null)
    {
        parent::__construct($name, $retryTimeout);

        $this->redis = $redis;
    }


    /**
     * @inheritDoc
     */
    public function shallWorkerRestart(CarbonInterface $workerStarted) : bool
    {
        $restartQueue = $this->makeRedisName(static::TAG_RESTART);
        $content = $this->redis->get($restartQueue);

        if ($content === null) return false;

        $timestamp = SimpleJSON::decode($content);
        return $workerStarted->getTimestamp() < $timestamp;
    }


    /**
     * @inheritDoc
     */
    public function signalWorkerRestart(?Duration $timeout = null) : void
    {
        $timestamp = Carbon::now()->getTimestamp();
        $timeout = $timeout ?? Duration::inSeconds(1800);

        $restartQueue = $this->makeRedisName(static::TAG_RESTART);
        $this->redis->set($restartQueue, SimpleJSON::encode($timestamp), $timeout);
    }


    /**
     * @inheritDoc
     */
    public function enqueue(Queueable $job) : void
    {
        $mainQueue = $this->makeRedisName();
        $notifyQueue = $this->makeRedisName(static::TAG_NOTIFY);
        $delayedQueue = $this->makeRedisName(static::TAG_DELAYED);
        $queueArguments = $job->getQueueArguments();

        // Determine maturity and create payload
        $jobMaturedAt = static::acceptQueueDelay($queueArguments->queueDelay);
        $payload = $this->encodeJob($job->getQueueTarget(), $queueArguments);

        if ($jobMaturedAt === null) {
            $this->redis->eval(RedisQueueScript::push(), $mainQueue, $notifyQueue, $payload);
        } else {
            $this->redis->sortedSetPush($delayedQueue, $jobMaturedAt->getTimestamp(), $payload);
        }
    }


    /**
     * @inheritDoc
     */
    public function enqueueFailed(FailedExecutableEncoded $failed) : void
    {
        $mainQueue = $this->makeRedisName();
        $notifyQueue = $this->makeRedisName(static::TAG_NOTIFY);

        // Recreate payload
        $decoded = SimpleJSON::decode($failed->encodedPayload);
        $parserHost = new ObjectParserHost($decoded);

        $serializedTarget = $parserHost->requires(RedisQueueEncodingKeys::TARGET, StringParser::create());

        $durationParser = Duration::createSecondParser();
        $queueArguments = new QueueArguments(
            $failed->id,
            $failed->name,
            $parserHost->requires(RedisQueueEncodingKeys::MAX_ATTEMPTS, IntegerParser::create()),
            null,   // Forced no longer delayed
            $parserHost->requires(RedisQueueEncodingKeys::RETRY_AFTER_SEC, $durationParser),
            $parserHost->requires(RedisQueueEncodingKeys::RUNNING_TIMEOUT_SEC, $durationParser),
            $this->name,
        );

        $payload = $this->encodeJob($serializedTarget, $queueArguments);
        $this->redis->eval(RedisQueueScript::push(), $mainQueue, $notifyQueue, $payload);
    }


    /**
     * @inheritDoc
     */
    public function dequeue(?Duration $timeout = null) : ?PendingExecutable
    {
        $this->migrateMatured();

        [$job, $reserved] = $this->dequeueRaw($this->retryTimeout, $timeout);

        if ($reserved) {
            return RedisPendingExecutable::decodeFrom($this, $job, $reserved);
        }

        return null;
    }


    /**
     * Delete a reserved job
     * @param string $reservedEncoded The reserved job's value (as stored and encoded in Redis)
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function deleteReserved(string $reservedEncoded) : void
    {
        $reservedQueue = $this->makeRedisName(static::TAG_RESERVED);
        $this->redis->sortedSetDelete($reservedQueue, $reservedEncoded);
    }


    /**
     * Repost a reserved job to be matured at a given time
     * @param string $reservedEncoded
     * @param CarbonInterface $maturedAt
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function repostReserved(string $reservedEncoded, CarbonInterface $maturedAt) : void
    {
        $delayedQueue = $this->makeRedisName(static::TAG_DELAYED);
        $reservedQueue = $this->makeRedisName(static::TAG_RESERVED);
        $maturedTimestamp = $maturedAt->getTimestamp();

        $this->redis->eval(RedisQueueScript::repostReserved(), $delayedQueue, $reservedQueue, $reservedEncoded, $maturedTimestamp);
    }


    /**
     * Migrate matured jobs
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    protected function migrateMatured() : void
    {
        $nowTimestamp = Carbon::now()->getTimestamp();

        $mainQueue = $this->makeRedisName();
        $notifyQueue = $this->makeRedisName(static::TAG_NOTIFY);

        $delayedQueue = $this->makeRedisName(static::TAG_DELAYED);
        $this->redis->eval(RedisQueueScript::migrateMatured(), $delayedQueue, $mainQueue, $notifyQueue, $nowTimestamp);

        $reservedQueue = $this->makeRedisName(static::TAG_RESERVED);
        $this->redis->eval(RedisQueueScript::migrateMatured(), $reservedQueue, $mainQueue, $notifyQueue, $nowTimestamp);
    }


    /**
     * Raw dequeue operation
     * @param Duration $retryTimeout
     * @param Duration|null $timeout
     * @return array<string|bool>
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    protected function dequeueRaw(Duration $retryTimeout, ?Duration $timeout) : array
    {
        $mainQueue = $this->makeRedisName();
        $reservedQueue = $this->makeRedisName(static::TAG_RESERVED);
        $notifyQueue = $this->makeRedisName(static::TAG_NOTIFY);
        $retryAt = static::acceptQueueDelay($retryTimeout);

        $dequeued = $this->redis->eval(RedisQueueScript::pop(), $mainQueue, $reservedQueue, $notifyQueue, $retryAt->getTimestamp());
        if (empty($dequeued)) return [null, null];

        [$job, $reserved] = $dequeued;

        if (!$job && ($timeout !== null)) {
            if ($this->redis->listPopFront($notifyQueue, RedisBlockingOptions::withTimeout($timeout))) {
                return $this->dequeueRaw($retryTimeout, null);
            }
        }

        return [$job, $reserved];
    }


    /**
     * Get the full redis name for the queue along with its tag
     * @param string|null $tag
     * @return string
     */
    protected function makeRedisName(?string $tag = null) : string
    {
        $ret = 'queues:' . $this->name;
        if (!is_empty_string($tag)) $ret .= ':' . $tag;

        return $ret;
    }


    /**
     * Encode the job
     * @param QueueRunnable|string $target
     * @param QueueArguments $queueArguments
     * @return string
     * @throws SafetyCommonException
     */
    protected function encodeJob(QueueRunnable|string $target, QueueArguments $queueArguments) : string
    {
        if ($target instanceof QueueRunnable) $target = serialize($target);

        return SimpleJSON::encode(obj([
            RedisQueueEncodingKeys::ID => $queueArguments->id,
            RedisQueueEncodingKeys::NAME => $queueArguments->name,
            RedisQueueEncodingKeys::RETRY_AFTER_SEC => $queueArguments->queueRetryDelay->getSeconds(),
            RedisQueueEncodingKeys::RUNNING_TIMEOUT_SEC => $queueArguments->queueRunningTimeout?->getSeconds() ?? 0,
            RedisQueueEncodingKeys::MAX_ATTEMPTS => $queueArguments->maxAttempts,
            RedisQueueEncodingKeys::ATTEMPTS => 0,
            RedisQueueEncodingKeys::TARGET => $target,
        ]));
    }
}