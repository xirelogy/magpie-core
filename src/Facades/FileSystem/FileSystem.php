<?php

namespace Magpie\Facades\FileSystem;

use Magpie\Exceptions\ClassNotOfTypeException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Contents\SimpleBinaryContent;
use Magpie\General\Factories\ClassFactory;
use Magpie\System\Concepts\SystemBootable;

/**
 * A file system interface
 */
abstract class FileSystem implements TypeClassable, SystemBootable
{
    /**
     * Constructor
     */
    protected function __construct()
    {

    }


    /**
     * If file of given path exist
     * @param string $path
     * @return bool
     */
    public abstract function isFileExist(string $path) : bool;


    /**
     * Read file content
     * @param string $path
     * @param array<FileSystemReadWriteOption> $options
     * @return BinaryDataProvidable
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public abstract function readFile(string $path, array $options = []) : BinaryDataProvidable;


    /**
     * Write file content
     * @param string $path
     * @param BinaryDataProvidable|string $data
     * @param array<FileSystemReadWriteOption> $options
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public abstract function writeFile(string $path, BinaryDataProvidable|string $data, array $options = []) : void;


    /**
     * Delete a file
     * @param string $path
     * @return bool
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function deleteFile(string $path) : bool;


    /**
     * If directory of given path exist
     * @param string $path
     * @return bool
     */
    public abstract function isDirectoryExist(string $path) : bool;


    /**
     * Create directory
     * @param string $path
     * @return bool If directory is newly created
     * @throws SafetyCommonException
     */
    public abstract function createDirectory(string $path) : bool;


    /**
     * Delete a directory
     * @param string $path
     * @return bool
     * @throws SafetyCommonException
     * @throws PersistenceException
     */
    public abstract function deleteDirectory(string $path) : bool;


    /**
     * Check the path
     * @param string $path
     * @return string|null
     */
    protected function checkPath(string $path) : ?string
    {
        $path = static::normalizePath($path);

        // Prevent traversal beyond root
        if (str_starts_with($path, '../') || ($path === '..')) return null;

        // Force prefix
        if (!str_starts_with($path, '/')) $path = '/' . $path;

        return $path;
    }


    /**
     * Initialize a file system
     * @param FileSystemConfig $config
     * @param string|null $typeClass
     * @return static
     * @throws SafetyCommonException
     */
    public static function initialize(FileSystemConfig $config, ?string $typeClass = null) : static
    {
        $finalTypeClass = $typeClass ?? $config->getTypeClass();

        $className = ClassFactory::resolve($finalTypeClass, self::class);
        if (!is_subclass_of($className, self::class)) throw new ClassNotOfTypeException($className, self::class);

        return $className::specificInitialize($config);
    }


    /**
     * Initialize a file system specifically for this type of adaptation
     * @param FileSystemConfig $config
     * @return static
     * @throws SafetyCommonException
     */
    protected static abstract function specificInitialize(FileSystemConfig $config) : static;


    /**
     * Wrap binary data into a simple binary data
     * @param string $data
     * @return BinaryDataProvidable
     */
    protected static function wrapData(string $data) : BinaryDataProvidable
    {
        return SimpleBinaryContent::create($data);
    }


    /**
     * Normalize a path
     * @param string $path
     * @param string $separator
     * @return string
     */
    public static function normalizePath(string $path, string $separator = '/') : string
    {
        $isBeginWithSeparator = str_starts_with($path, $separator);

        /** @var array<string> $subPaths */
        $subPaths = array_Filter(explode($separator, $path), mb_strlen(...));

        $absolutes = [];
        foreach ($subPaths as $subPath) {
            // Current path is ignored
            if ($subPath === '.') continue;

            // Handle previous path
            if ($subPath === '..') {
                if (!$isBeginWithSeparator && empty(array_filter($absolutes, fn (string $value) => !($value === '..')))) {
                    $absolutes[] = $subPath;
                } else if (count($absolutes) > 0) {
                    // Only pop when available
                    array_pop($absolutes);
                } else {
                    // Otherwise, always considered not beginning with a separator
                    $isBeginWithSeparator = false;
                    $absolutes[] = $subPath;
                }
                continue;
            }

            // Append
            $absolutes[] = $subPath;
        }

        $ret = $isBeginWithSeparator ? $separator : '';
        return $ret . implode($separator, $absolutes);
    }
}