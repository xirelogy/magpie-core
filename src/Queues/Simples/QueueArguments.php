<?php

namespace Magpie\Queues\Simples;

use Carbon\CarbonInterface;
use Magpie\General\DateTimes\Duration;

/**
 * Queue arguments
 */
class QueueArguments
{
    /**
     * @var string|int Job ID
     */
    public string|int $id;
    /**
     * @var string Queue item name
     */
    public string $name;
    /**
     * @var int Maximum number of attempts
     */
    public int $maxAttempts;
    /**
     * @var Duration|CarbonInterface|null Queue delay
     */
    public Duration|CarbonInterface|null $queueDelay;
    /**
     * @var Duration Queue retry delay
     */
    public Duration $queueRetryDelay;
    /**
     * @var Duration|null Queue maximum running timeout
     */
    public ?Duration $queueRunningTimeout;
    /**
     * @var string|null Queue name
     */
    public ?string $queueName;


    /**
     * Constructor
     * @param string|int $id
     * @param string $name
     * @param int $maxAttempts
     * @param Duration|CarbonInterface|null $queueDelay
     * @param Duration $queueRetryDelay
     * @param Duration|null $queueRunningTimeout
     * @param string|null $queueName
     */
    public function __construct(string|int $id, string $name, int $maxAttempts, Duration|CarbonInterface|null $queueDelay, Duration $queueRetryDelay, ?Duration $queueRunningTimeout, ?string $queueName)
    {
        $this->id = $id;
        $this->name = $name;
        $this->maxAttempts = $maxAttempts;
        $this->queueDelay = $queueDelay;
        $this->queueRetryDelay = $queueRetryDelay;
        $this->queueRunningTimeout = $queueRunningTimeout;
        $this->queueName = $queueName;
    }
}