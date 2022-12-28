<?php

namespace Magpie\Logs;

use Magpie\Logs\Concepts\LogRelayable;
use Magpie\Logs\Relays\SplitLogRelay;

/**
 * Default log relay interface
 */
abstract class LogRelay implements LogRelayable
{
    /**
     * @var LogConfig Log configuration
     */
    public LogConfig $config;
    /**
     * @var string|null Current source
     */
    protected ?string $source;


    /**
     * Constructor
     * @param LogConfig $config
     * @param string|null $source
     */
    public function __construct(LogConfig $config, ?string $source)
    {
        $this->config = $config;
        $this->source = $source;
    }


    /**
     * @inheritDoc
     */
    public function getSource() : ?string
    {
        return $this->source;
    }


    /**
     * @inheritDoc
     */
    public function split(string $source) : LogRelayable
    {
        $fullSource = SplitLogRelay::fullSource($this->source, $source);
        return new SplitLogRelay($this, $fullSource);
    }
}