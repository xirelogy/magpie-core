<?php

namespace Magpie\General\Concepts;

/**
 * Interface to anything that can provide a file system access
 */
interface FileSystemAccessible
{
    /**
     * Path when accessing from file system
     * @return string
     */
    public function getFileSystemPath() : string;
}