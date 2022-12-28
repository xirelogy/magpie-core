<?php

namespace Magpie\Facades\FileSystem\Providers\Local;

use Magpie\Exceptions\InvalidDataException;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Facades\FileSystem\FileSystemConfig;
use Magpie\General\Traits\SingletonInstance;

/**
 * A local file system
 */
class LocalRootFileSystem extends LocalFileSystem
{
    use SingletonInstance;


    /**
     * Constructor
     */
    protected function __construct()
    {
        parent::__construct(new LocalFileSystemConfig('/'));
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitialize(FileSystemConfig $config) : static
    {
        if (!$config instanceof LocalFileSystemConfig) throw new NotOfTypeException($config, LocalFileSystemConfig::class);
        if ($config->rootPath !== '/') throw new InvalidDataException();

        return new static();
    }
}