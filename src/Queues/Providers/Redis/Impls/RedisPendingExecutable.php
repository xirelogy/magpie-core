<?php

namespace Magpie\Queues\Providers\Redis\Impls;

use Carbon\CarbonInterface;
use Exception;
use Magpie\Codecs\ParserHosts\ObjectParserHost;
use Magpie\Codecs\Parsers\ClosureParser;
use Magpie\Codecs\Parsers\IntegerParser;
use Magpie\Codecs\Parsers\StringParser;
use Magpie\Exceptions\ArgumentException;
use Magpie\Exceptions\InvalidJsonDataFormatException;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\General\DateTimes\Duration;
use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Sugars\Excepts;
use Magpie\Queues\Concepts\QueueRunnable;
use Magpie\Queues\PendingExecutable;
use Magpie\Queues\Providers\Redis\RedisQueue;
use Magpie\Queues\Simples\FailedExecutableEncoded;
use Throwable;

/**
 * A pending executable unit from the queue for Redis implementation
 * @internal
 */
class RedisPendingExecutable extends PendingExecutable
{
    /**
     * @var RedisQueue Associated queue
     */
    protected readonly RedisQueue $queue;
    /**
     * @var QueueRunnable The target runnable
     */
    protected readonly QueueRunnable $target;
    /**
     * @var ObjectParserHost Associated parser host
     */
    protected readonly ObjectParserHost $parserHost;
    /**
     * @var string Original job's encoded string
     */
    protected readonly string $jobEncoded;
    /**
     * @var string Reserved content, encoded
     */
    protected readonly string $reservedEncoded;


    /**
     * Constructor
     * @param RedisQueue $queue
     * @param ObjectParserHost $parserHost
     * @param string $jobEncoded
     * @param string $reservedEncoded
     * @throws ArgumentException
     */
    protected function __construct(RedisQueue $queue, ObjectParserHost $parserHost, string $jobEncoded, string $reservedEncoded)
    {
        $this->queue = $queue;
        $this->target = static::parseTargetPayload($parserHost);
        $this->parserHost = $parserHost;
        $this->jobEncoded = $jobEncoded;
        $this->reservedEncoded = $reservedEncoded;
    }


    /**
     * @inheritDoc
     */
    public final function getId() : string|int
    {
        try {
            return $this->parserHost->requires(RedisQueueEncodingKeys::ID);
        } catch (Exception) {
            return '';
        }
    }


    /**
     * @inheritDoc
     */
    public final function getName() : string
    {
        try {
            return $this->parserHost->requires(RedisQueueEncodingKeys::NAME);
        } catch (Exception) {
            return '';
        }
    }


    /**
     * @inheritDoc
     */
    protected function getCurrentAttempt() : int
    {
        return $this->parserHost->requires(RedisQueueEncodingKeys::ATTEMPTS, IntegerParser::create()) + 1;
    }


    /**
     * @inheritDoc
     */
    protected function getMaxAttempts() : int
    {
        return $this->parserHost->requires(RedisQueueEncodingKeys::MAX_ATTEMPTS, IntegerParser::create());
    }


    /**
     * @inheritDoc
     */
    protected function releaseFromQueue() : void
    {
        Excepts::noThrow(fn () => $this->queue->deleteReserved($this->reservedEncoded));
    }


    /**
     * @inheritDoc
     */
    protected function repostLaterOnQueue(CarbonInterface $matureAt) : void
    {
        Excepts::noThrow(function () use ($matureAt) : void {
            $this->queue->repostReserved($this->reservedEncoded, $matureAt);
        });
    }


    /**
     * @inheritDoc
     */
    protected function getRetryDelay() : Duration|CarbonInterface
    {
        $durationParser = Duration::createSecondParser();

        return $this->parserHost->requires(RedisQueueEncodingKeys::RETRY_AFTER_SEC, $durationParser);
    }


    /**
     * @inheritDoc
     */
    protected function getRunningTimeout() : Duration
    {
        $durationParser = Duration::createSecondParser();

        return $this->parserHost->requires(RedisQueueEncodingKeys::RUNNING_TIMEOUT_SEC, $durationParser);
    }


    /**
     * @inheritDoc
     */
    protected function getTarget() : QueueRunnable
    {
        return $this->target;
    }


    /**
     * @inheritDoc
     */
    protected function encodeFailed(CarbonInterface $happenedAt, Throwable $ex) : FailedExecutableEncoded
    {
        $queue = $this->queue->getName();
        $id = $this->getId();
        $name = $this->getName();
        $encoded = $this->jobEncoded;

        return new FailedExecutableEncoded($queue, $id, $name, $encoded, $happenedAt, $ex);
    }


    /**
     * Decode from given text contents
     * @param RedisQueue $queue
     * @param string $jobEncoded
     * @param string $reservedEncoded
     * @return static
     * @throws ArgumentException
     * @throws InvalidJsonDataFormatException
     */
    public static function decodeFrom(RedisQueue $queue, string $jobEncoded, string $reservedEncoded) : static
    {
        $jobDecoded = SimpleJSON::decode($jobEncoded);
        $decodedParserHost = new ObjectParserHost($jobDecoded);

        return new static($queue, $decodedParserHost, $jobEncoded, $reservedEncoded);
    }


    /**
     * Extract and parse payload
     * @param ObjectParserHost $parserHost
     * @return QueueRunnable
     * @throws ArgumentException
     */
    protected static function parseTargetPayload(ObjectParserHost $parserHost) : QueueRunnable
    {
        $targetParser = ClosureParser::create(function (mixed $value, ?string $hintName) : QueueRunnable {
            $value = StringParser::create()->parse($value, $hintName);
            $ret = unserialize($value);
            if (!$ret instanceof QueueRunnable) throw new NotOfTypeException($ret, QueueRunnable::class);

            return $ret;
        });

        return $parserHost->requires(RedisQueueEncodingKeys::TARGET, $targetParser);
    }
}