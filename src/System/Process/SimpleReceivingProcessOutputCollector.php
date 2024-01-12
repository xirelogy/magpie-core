<?php

namespace Magpie\System\Process;

use Magpie\System\Concepts\ProcessOutputCollectable;

/**
 * Simple process output collector that only focus on receiving
 */
abstract class SimpleReceivingProcessOutputCollector implements ProcessOutputCollectable
{
    /**
     * @inheritDoc
     */
    public final function close() : void
    {
        // NOP
    }


    /**
     * @inheritDoc
     */
    public final function export(ProcessStandardStream $stream) : ?string
    {
        return null;
    }
}