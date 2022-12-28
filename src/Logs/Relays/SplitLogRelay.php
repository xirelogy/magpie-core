<?php

namespace Magpie\Logs\Relays;

use Magpie\Logs\LogEntry;
use Magpie\Logs\LogRelay;

/**
 * Split out log relay
 */
class SplitLogRelay extends LogRelay
{
    /**
     * @var LogRelay Parent log relay
     */
    protected LogRelay $parent;


    /**
     * Constructor
     * @param LogRelay $parent
     * @param string $source
     */
    public function __construct(LogRelay $parent, string $source)
    {
        parent::__construct($parent->config, $source);

        $this->parent = $parent;
    }


    /**
     * @inheritDoc
     */
    public function log(LogEntry $record) : void
    {
        $this->parent->log($record);
    }


    /**
     * Full source combining current and next
     * @param string|null $currentSource
     * @param string $nextSource
     * @return string
     */
    public static function fullSource(?string $currentSource, string $nextSource) : string
    {
        if (is_empty_string($currentSource)) return $nextSource;

        return "$currentSource.$nextSource";
    }
}
