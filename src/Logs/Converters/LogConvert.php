<?php

namespace Magpie\Logs\Converters;

use Magpie\General\Concepts\StreamReadable;
use Magpie\General\IOs\IterableReadStream;
use Magpie\Logs\LogEntry;

/**
 * May convert log files into specific format
 */
abstract class LogConvert
{
    /**
     * Convert the target records into a stream of output
     * @param iterable<LogEntry> $records
     * @return StreamReadable
     */
    public final function convert(iterable $records) : StreamReadable
    {
        return IterableReadStream::create($this->convertChunks($records));
    }


    /**
     * Convert using target records into chunks
     * @param iterable<LogEntry> $records
     * @return iterable<string>
     */
    private function convertChunks(iterable $records) : iterable
    {
        yield from $this->convertPrefixes();
        foreach ($records as $record) {
            yield from $this->convertRecord($record);
        }
        yield from $this->convertSuffixes();
    }


    /**
     * All format prefixes
     * @return iterable<string>
     */
    protected function convertPrefixes() : iterable
    {
        return [];
    }


    /**
     * Convert a record
     * @param LogEntry $record
     * @return iterable<string>
     */
    protected abstract function convertRecord(LogEntry $record) : iterable;


    /**
     * All format suffixes
     * @return iterable<string>
     */
    protected function convertSuffixes() : iterable
    {
        return [];
    }
}