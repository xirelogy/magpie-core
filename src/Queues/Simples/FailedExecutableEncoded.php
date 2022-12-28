<?php

namespace Magpie\Queues\Simples;

use Carbon\CarbonInterface;
use Throwable;

/**
 * Encoded content to represent a failed executable job
 */
class FailedExecutableEncoded
{
    /**
     * @var string Name of the queue handling the job
     */
    public string $queue;
    /**
     * @var string|int Job ID
     */
    public string|int $id;
    /**
     * @var string Job name
     */
    public string $name;
    /**
     * @var string Encoded payload
     */
    public string $encodedPayload;
    /**
     * @var CarbonInterface When the failure happened
     */
    public CarbonInterface $happenedAt;
    /**
     * @var Throwable|ExceptionEncoded|null The associated exception
     */
    public Throwable|ExceptionEncoded|null $exception;


    /**
     * Constructor
     * @param string $queue
     * @param string|int $id
     * @param string $name
     * @param string $encodedPayload
     * @param CarbonInterface $happenedAt
     * @param Throwable|ExceptionEncoded|null $exception
     */
    public function __construct(string $queue, string|int $id, string $name, string $encodedPayload, CarbonInterface $happenedAt, Throwable|ExceptionEncoded|null $exception = null)
    {
        $this->queue = $queue;
        $this->id = $id;
        $this->name = $name;
        $this->encodedPayload = $encodedPayload;
        $this->happenedAt = $happenedAt;
        $this->exception = $exception;
    }


    /**
     * The effective exception (always encoded)
     * @return ExceptionEncoded|null
     */
    public final function getExceptionEncoded() : ?ExceptionEncoded
    {
        if ($this->exception instanceof ExceptionEncoded) return $this->exception;
        if ($this->exception === null) return null;

        return new ExceptionEncoded(
            $this->exception::class,
            $this->exception->getMessage(),
            $this->exception->getTraceAsString(),
        );
    }
}