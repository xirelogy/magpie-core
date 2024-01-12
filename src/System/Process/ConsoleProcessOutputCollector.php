<?php

namespace Magpie\System\Process;

use Magpie\Facades\Console;

/**
 * Receive and redirect process output directly to the console
 */
class ConsoleProcessOutputCollector extends SimpleReceivingProcessOutputCollector
{
    /**
     * @inheritDoc
     */
    public function receive(ProcessStandardStream $stream, string $content) : void
    {
        $content = rtrim($content, "\r\n");

        switch ($stream) {
            case ProcessStandardStream::OUTPUT:
                Console::info($content);
                break;
            case ProcessStandardStream::ERROR:
                Console::warning($content);
                break;
            default:
                // NOP
                break;
        }
    }
}