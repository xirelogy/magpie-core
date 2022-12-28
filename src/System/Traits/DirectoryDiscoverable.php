<?php

namespace Magpie\System\Traits;

use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\System\Kernel\ExceptionHandler;

/**
 * May discover from various directories
 */
trait DirectoryDiscoverable
{
    /**
     * @var array<string, string> Directories to be included for discovery
     */
    protected static array $discoverDirectories = [];


    /**
     * Include given directory for discovery of factory type classes
     * @param string $path
     * @return void
     */
    public final static function includeDirectory(string $path) : void
    {
        $realPath = realpath($path);
        if ($realPath === false) ExceptionHandler::systemCritical("'$path' is an invalid path");
        if (!LocalRootFileSystem::instance()->isDirectoryExist($realPath)) ExceptionHandler::systemCritical("'$path' is not a valid directory");

        if (array_key_exists($realPath, static::$discoverDirectories)) return;

        static::$discoverDirectories[$realPath] = $realPath;
    }
}