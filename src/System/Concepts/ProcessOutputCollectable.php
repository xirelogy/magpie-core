<?php

namespace Magpie\System\Concepts;

use Magpie\General\Concepts\Closeable;
use Magpie\General\Concepts\StreamReadable;
use Magpie\General\Concepts\StreamReadConvertible;
use Magpie\System\Process\ProcessStandardStream;

/**
 * May receive output from process
 */
interface ProcessOutputCollectable extends Closeable
{
    /**
     * Receive content from process's stream
     * @param ProcessStandardStream $stream
     * @param string $content
     * @return void
     */
    public function receive(ProcessStandardStream $stream, string $content) : void;


    /**
     * Export the content received for given process's stream
     * @param ProcessStandardStream $stream
     * @return StreamReadable|StreamReadConvertible|iterable<string>|string|null
     */
    public function export(ProcessStandardStream $stream) : StreamReadable|StreamReadConvertible|iterable|string|null;
}