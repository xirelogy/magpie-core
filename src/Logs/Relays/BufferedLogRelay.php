<?php

namespace Magpie\Logs\Relays;

use Carbon\Carbon;
use Magpie\Logs\LogEntry;
use Magpie\Logs\LogRelay;
use Magpie\System\Kernel\Kernel;

/**
 * Log relay that buffers the received record entries for further processing use
 */
class BufferedLogRelay extends LogRelay
{
    /**
     * @var array<LogEntry> Buffered log record entries
     */
    protected array $records = [];


    /**
     * Constructor
     * @param string|null $source
     */
    public function __construct(?string $source)
    {
        // Config is not important, always use default
        $config = Kernel::current()->getConfig()->createDefaultLogConfig();

        parent::__construct($config, $source);
    }


    /**
     * @inheritDoc
     */
    public function log(LogEntry $record) : void
    {
        $newRecord = new LogEntry(
            $record->source,
            $record->level,
            $record->message,
            $record->context,
            $record->loggedAt ?? Carbon::now(),
        );

        $this->records[] = $newRecord;
    }


    /**
     * Clear the buffer
     * @return void
     */
    public function clear() : void
    {
        $this->records = [];
    }


    /**
     * Export all entries to target relay
     * @param LogRelay $relay
     * @return void
     */
    public function export(LogRelay $relay) : void
    {
        foreach ($this->records as $record) {
            $relay->log($record);
        }
    }


    /**
     * Access to all the buffered record entries
     * @return iterable<LogEntry>
     */
    public function getRecords() : iterable
    {
        yield from $this->records;
    }
}