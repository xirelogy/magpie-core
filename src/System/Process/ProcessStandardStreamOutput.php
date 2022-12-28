<?php

namespace Magpie\System\Process;

final class ProcessStandardStreamOutput
{
    /**
     * @var ProcessStandardStream Stream where current output is received on
     */
    public ProcessStandardStream $stream;
    /**
     * @var string Received content
     */
    public string $content;


    /**
     * Constructor
     * @param ProcessStandardStream $stream
     * @param string $content
     */
    public function __construct(ProcessStandardStream $stream, string $content)
    {
        $this->stream = $stream;
        $this->content = $content;
    }
}