<?php

namespace Magpie\System\Impls;

use Magpie\Exceptions\InvalidStateException;
use Magpie\General\Arr;
use Magpie\General\Concepts\Dispatchable;
use Magpie\General\DateTimes\Duration;
use Magpie\General\DateTimes\Specific\DurationInMicroseconds;
use Magpie\General\Traits\SingletonInstance;
use Magpie\System\Concepts\MainLoopPollable;

/**
 * Main loop instance
 * @internal
 */
final class MainLoopInstance
{
    use SingletonInstance;

    /**
     * @var bool If instance is running
     */
    protected bool $isRunning = false;
    /**
     * @var array<MainLoopPollable> Polls to be polling during the loop
     */
    protected array $polls = [];
    /**
     * @var mixed|null Last return result
     */
    protected mixed $lastResult = null;


    /**
     * Register a poll to the loop
     * @param MainLoopPollable $poll
     * @return bool
     */
    public function registerPoll(MainLoopPollable $poll) : bool
    {
        if (in_array($poll, $this->polls)) return false;

        $index = $this->findInsertionIndex($poll);
        Arr::insert($this->polls, $poll, $index);

        return true;
    }


    /**
     * Deregister a poll from the loop
     * @param MainLoopPollable $poll
     * @return bool
     */
    public function deregisterPoll(MainLoopPollable $poll) : bool
    {
        return Arr::deleteByValue($this->polls, $poll) > 0;
    }


    /**
     * Find a proper insertion index according to candidate's priority
     * @param MainLoopPollable $candidatePoll
     * @return int|null
     */
    protected function findInsertionIndex(MainLoopPollable $candidatePoll) : ?int
    {
        $candidatePriority = $candidatePoll->getPriority();

        $index = 0;
        foreach ($this->polls as $poll) {
            if ($candidatePriority < $poll->getPriority()) return $index;
            ++$index;
        }

        return null;
    }


    /**
     * Run the main loop
     * @return mixed
     * @throws InvalidStateException
     */
    public function run() : mixed
    {
        if ($this->isRunning) throw new InvalidStateException();

        $this->isRunning = true;
        $this->lastResult = null;


        do {
            $hasItem = false;
            foreach ($this->poll($hasPoll, $lastIdlePoll) as $item) {
                $hasItem = true;
                $item->dispatch();
            }

            if (!$hasItem) {
                foreach ($this->idlePoll($lastIdlePoll) as $item) {
                    $item->dispatch();
                }
            }
        } while ($hasPoll && $this->isRunning);

        // Main loop is marked as no longer running
        $this->isRunning = false;

        return $this->lastResult;
    }


    /**
     * Send in termination signal
     * @return void
     */
    public function terminate() : void
    {
        $this->isRunning = false;
    }


    /**
     * Poll for dispatchable items from the available polls
     * @param bool|null $hasPoll
     * @param MainLoopPollable|null $lastIdlePoll
     * @return iterable<Dispatchable>
     */
    protected function poll(?bool &$hasPoll = null, ?MainLoopPollable &$lastIdlePoll = null) : iterable
    {
        $hasPoll = false;

        foreach ($this->polls as $poll) {
            $hasPoll = true;
            if ($poll->isSupportIdle()) $lastIdlePoll = $poll;
            yield from $poll->poll(null);
        }
    }


    /**
     * @param MainLoopPollable|null $idlePoll
     * @return iterable<Dispatchable>
     */
    protected function idlePoll(?MainLoopPollable $idlePoll) : iterable
    {
        $idleDuration = $this->getIdleDuration();

        if ($idlePoll !== null) {
            yield from $idlePoll->poll($idleDuration);
        } else {
            $uSec = $idleDuration->getValueAtPrecisionScale(DurationInMicroseconds::SCALE);
            if ($uSec <= 0) $uSec = 1;
            usleep($uSec);
        }
    }


    /**
     * Duration to stay idle
     * @return Duration
     */
    protected function getIdleDuration() : Duration
    {
        return Duration::inMicroseconds(100);    // FIXME
    }
}