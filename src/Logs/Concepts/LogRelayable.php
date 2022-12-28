<?php

namespace Magpie\Logs\Concepts;

use Magpie\Logs\LogEntry;

/**
 * Anything that can relay (or handle) log messages
 */
interface LogRelayable
{
    /**
     * Relay's source
     * @return string|null
     */
    public function getSource() : ?string;


    /**
     * Receive and relay log message
     * @param LogEntry $record
     * @return void
     */
    public function log(LogEntry $record) : void;


    /**
     * Split out an individual interface to log under specific sub-source
     * @param string $source
     * @return LogRelayable
     */
    public function split(string $source) : LogRelayable;
}