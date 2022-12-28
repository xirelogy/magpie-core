<?php

namespace Magpie\Queues;

use Carbon\CarbonInterface;
use Exception;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Facades\Log;
use Magpie\General\DateTimes\Duration;
use Magpie\Queues\Concepts\Queueable;
use Magpie\Queues\Concepts\QueueIdentityProvidable;
use Magpie\Queues\Concepts\QueueRunnable;
use Magpie\Queues\Providers\QueueCreator;
use Magpie\Queues\Simples\QueueArguments;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\Kernel;

/**
 * Pending queueable
 */
class PendingQueueable implements Queueable
{
    /**
     * Default maximum job running time
     */
    public const DEFAULT_RUNNING_TIMEOUT_SEC = 120;
    /**
     * Default delay for retry, in seconds
     */
    public const DEFAULT_RETRY_DELAY_SEC = 90;
    /**
     * Default number of max attempts
     */
    public const DEFAULT_MAX_ATTEMPTS = 3;

    /**
     * @var string|int Unique identity of current item
     */
    protected readonly string|int $id;
    /**
     * @var QueueRunnable Runnable target
     */
    public readonly QueueRunnable $target;
    /**
     * @var string|null Specific name for the runnable target
     */
    protected ?string $name = null;
    /**
     * @var int Maximum number of attempts
     */
    protected int $maxAttempts = self::DEFAULT_MAX_ATTEMPTS;
    /**
     * @var string|null Name of queue to be queued to
     */
    protected ?string $queueName = null;
    /**
     * @var Duration|CarbonInterface|null Delay before mature (executable)
     */
    protected Duration|CarbonInterface|null $queueDelay = null;
    /**
     * @var Duration|null Maximum job running time
     */
    protected ?Duration $queueRunningTimeout = null;
    /**
     * @var Duration|null Delay before retry
     */
    protected ?Duration $queueRetryDelay = null;
    /**
     * @var bool If current pending queueable posted
     */
    private bool $isPosted = false;


    /**
     * Constructor
     * @param QueueRunnable $target
     */
    public function __construct(QueueRunnable $target)
    {
        $this->id = static::generateId();
        $this->queueRunningTimeout = Duration::inSeconds(static::DEFAULT_RUNNING_TIMEOUT_SEC);
        $this->target = $target;
    }


    /**
     * Destructor
     */
    public function __destruct()
    {
        try {
            $this->post();
        } catch (Exception $ex) {
            Log::warning($ex->getMessage());
        }
    }


    /**
     * @inheritDoc
     */
    public function getId() : string|int
    {
        return $this->id;
    }


    /**
     * Post current job
     * @return bool
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public function post() : bool
    {
        if ($this->isPosted) return false;
        $this->isPosted = true;

        $queue = QueueCreator::instance()->getQueue($this->queueName);
        $queue->enqueue($this);

        return true;
    }


    /**
     * @inheritDoc
     */
    public function withName(string $name) : static
    {
        $this->name = $name;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withMaxAttempts(int $attempts) : static
    {
        $this->maxAttempts = $attempts;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withRetryDelay(Duration $delay) : static
    {
        $this->queueRetryDelay = $delay;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withRunningTimeout(?Duration $timeout) : static
    {
        $this->queueRunningTimeout = $timeout;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withQueue(string $name) : static
    {
        $this->queueName = $name;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function withDelay(Duration|CarbonInterface $delay) : static
    {
        $this->queueDelay = $delay;
        return $this;
    }


    /**
     * @inheritDoc
     */
    public function getQueueArguments() : QueueArguments
    {
        $name = $this->name ?? $this->target::class;
        $queueDelay = $this->queueDelay;
        $queueRetryDelay = $this->queueRetryDelay ?? Duration::inSeconds(static::DEFAULT_RETRY_DELAY_SEC);

        return new QueueArguments($this->id, $name, $this->maxAttempts, $queueDelay, $queueRetryDelay, $this->queueRunningTimeout, $this->queueName);
    }


    /**
     * @inheritDoc
     */
    public function getQueueTarget() : QueueRunnable
    {
        return $this->target;
    }


    /**
     * @inheritDoc
     */
    public function run() : void
    {
        $this->target->run();
    }


    /**
     * Generate a unique identity
     * @return string|int
     */
    protected static function generateId() : string|int
    {
        try {
            $provider = Kernel::current()->getProvider(QueueIdentityProvidable::class);
            if (!$provider instanceof QueueIdentityProvidable) throw new NotOfTypeException($provider, QueueIdentityProvidable::class);
            return $provider->generateId();
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }
}