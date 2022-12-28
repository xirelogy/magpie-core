<?php

namespace Magpie\System\Process;

use Magpie\System\Concepts\ProcessOutputCollectable;

/**
 * Receive and collect process output in memory buffer
 */
class BufferedProcessOutputCollector implements ProcessOutputCollectable
{
    /**
     * @var bool If collector may receive
     */
    protected bool $isReceiving = true;
    /**
     * @var string Buffer for output
     */
    protected string $outputBuffer = '';
    /**
     * @var string Buffer for error
     */
    protected string $errorBuffer = '';


    /**
     * @inheritDoc
     */
    public function close() : void
    {
        $this->isReceiving = false;
    }


    /**
     * @inheritDoc
     */
    public function receive(ProcessStandardStream $stream, string $content) : void
    {
        if (!$this->isReceiving) return;

        switch ($stream) {
            case ProcessStandardStream::OUTPUT:
                $this->outputBuffer .= $content;
                break;
            case ProcessStandardStream::ERROR:
                $this->errorBuffer .= $content;
                break;
            default:
                break;
        }
    }


    /**
     * @inheritDoc
     */
    public function export(ProcessStandardStream $stream) : ?string
    {
        return match ($stream) {
            ProcessStandardStream::OUTPUT => $this->outputBuffer,
            ProcessStandardStream::ERROR => $this->errorBuffer,
            default => null,
        };
    }
}