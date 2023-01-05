<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\UnsupportedException;
use Magpie\General\Sugars\Excepts;
use Magpie\Logs\Concepts\Loggable;
use Magpie\Models\Concepts\DirectTransactionable;
use Magpie\Models\Concepts\TransactionCompletedListenable;
use Magpie\Models\Connection;
use Magpie\Models\Exceptions\ModelSafetyException;
use Magpie\Models\Identifier;

/**
 * Support for stacked database transaction
 * @internal
 */
class TransactionStack
{
    /**
     * @var array<string, static> Initialized instances
     */
    protected static array $instances = [];
    /**
     * @var Loggable|null Track logger
     */
    protected static ?Loggable $logger = null;
    /**
     * @var Connection Associated connection
     */
    protected readonly Connection $connection;
    /**
     * @var DirectTransactionable Service interface
     */
    protected readonly DirectTransactionable $service;
    /**
     * @var bool If acceptance is blocked
     */
    protected bool $isBlockAccept = false;
    /**
     * @var array<bool|null> Previous accept status
     */
    protected array $acceptedStack = [];
    /**
     * @var int Last index
     */
    protected int $lastIndex = 0;
    /**
     * @var bool|null If last status is accepted
     */
    protected ?bool $lastIsAccepted = null;
    /**
     * @var array<TransactionCompletedListenable> Receivers to be notified on completion
     */
    protected array $completedListeners = [];


    /**
     * Constructor
     * @param Connection $connection
     */
    protected function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->service = $connection->getDirectTransaction();
    }


    /**
     * Acquire a transaction
     * @return int
     * @throws ModelSafetyException
     * @throws UnsupportedException
     */
    public function acquire() : int
    {
        if ($this->lastIndex === 0) {
            $this->service->beginTransaction();
        }

        $this->acceptedStack[] = $this->lastIsAccepted;
        $this->lastIsAccepted = null;

        ++$this->lastIndex;
        static::track($this->connection, 'acquired', $this->lastIndex);

        return $this->lastIndex;
    }


    /**
     * Mark as accepted
     * @param int $index
     * @return void
     */
    public function accept(int $index) : void
    {
        if ($index !== $this->lastIndex) return;
        static::track($this->connection, 'accepted', $index);
        $this->lastIsAccepted = true;
    }


    /**
     * Release transaction at index
     * @param int $index
     * @return void
     * @throws ModelSafetyException
     * @throws UnsupportedException
     */
    public function release(int $index) : void
    {
        static::track($this->connection, 'releasing', $index);
        if ($index === $this->lastIndex) {
            if (!$this->lastIsAccepted) {
                $this->isBlockAccept = true;
            }
        } else {
            $this->isBlockAccept = true;
        }

        --$this->lastIndex;

        if ($this->lastIndex === 0) {
            // Transaction operation when all released
            if (!$this->isBlockAccept) {
                static::track($this->connection, 'commit');
                $this->service->commit();
                $this->notifyCompleted(true);
            } else {
                static::track($this->connection, 'rollback');
                $this->service->rollback();
                $this->notifyCompleted(false);
            }
        } else {
            // Unstack
            $this->lastIsAccepted = array_pop($this->acceptedStack);
        }
    }


    /**
     * Subscribe to receive notification on completion
     * @param TransactionCompletedListenable $listener
     * @return void
     */
    public function subscribeCompleted(TransactionCompletedListenable $listener) : void
    {
        $this->completedListeners[] = $listener;
    }


    /**
     * Notify completion status
     * @param bool $isCommitted
     * @return void
     */
    private function notifyCompleted(bool $isCommitted) : void
    {
        static::track($this->connection, 'notifyingCompleted (' . ($isCommitted ? 'T' : 'F') . ')');

        // Transfer to localized list of notification target
        $completedListeners = $this->completedListeners;
        $this->completedListeners = [];

        // Dispatch notifications
        foreach ($completedListeners as $completedListener) {
            Excepts::noThrow(fn () => $completedListener->notifyCompleted($isCommitted));
        }
    }


    /**
     * Get instance for given connection
     * @param Connection $connection
     * @return static
     */
    public static function instanceOf(Connection $connection) : static
    {
        $id = $connection->getId();

        if (!array_key_exists($id, static::$instances)) {
            static::$instances[$id] = new static($connection);
        }

        return static::$instances[$id];
    }


    /**
     * Track the transaction stack status
     * @param Connection $connection
     * @param string $message
     * @param int|null $index
     * @return void
     */
    protected static function track(Connection $connection, string $message, ?int $index = null) : void
    {
        if (static::$logger === null) return;

        $connectionId = Identifier::toString($connection->getId());

        if ($index !== null) {
            static::$logger->debug("[$connectionId] $message: $index");
        } else {
            static::$logger->debug("[$connectionId] $message");
        }
    }


    /**
     * Specify a logger to receive transaction debugging logs
     * @param Loggable|null $logger
     * @return void
     */
    public static function setLogger(?Loggable $logger) : void
    {
        static::$logger = $logger;
    }
}