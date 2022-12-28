<?php

namespace Magpie\System\Process;

/**
 * Standard input/output streams of a process
 */
enum ProcessStandardStream : string
{
    /**
     * Standard input stream (stdin)
     */
    case INPUT = 'input';
    /**
     * Standard output stream (stdout)
     */
    case OUTPUT = 'output';
    /**
     * Standard error stream (stderr)
     */
    case ERROR = 'error';
}