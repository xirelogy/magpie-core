<?php

namespace Magpie\Facades\FileSystem\Providers\Local;

use Exception;
use Magpie\Exceptions\FileNotFoundException;
use Magpie\Exceptions\FileOperationFailedException;
use Magpie\Exceptions\NotOfTypeException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Facades\FileSystem\FileSystem;
use Magpie\Facades\FileSystem\FileSystemConfig;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\General\Factories\ClassFactory;
use Magpie\System\Kernel\BootContext;
use Magpie\System\Kernel\BootRegistrar;

/**
 * Local file system
 */
#[FactoryTypeClass(LocalFileSystem::TYPECLASS, FileSystem::class)]
class LocalFileSystem extends FileSystem
{
    /**
     * Current type class
     */
    public const TYPECLASS = 'local';

    /**
     * @var LocalFileSystemConfig Associated configuration
     */
    protected LocalFileSystemConfig $config;


    /**
     * Constructor
     * @param LocalFileSystemConfig $config
     */
    protected function __construct(LocalFileSystemConfig $config)
    {
        parent::__construct();

        $this->config = $config;
    }


    /**
     * @inheritDoc
     */
    public function isFileExist(string $path) : bool
    {
        $path = $this->checkPath($path);
        if ($path === null) return false;

        if (!file_exists($path)) return false;
        if (!is_file($path)) return false;

        return true;
    }


    /**
     * @inheritDoc
     */
    public function readFile(string $path, array $options = []) : BinaryDataProvidable
    {
        $checkedPath = $this->checkPath($path);
        if ($checkedPath === null) throw new FileNotFoundException($path);

        if (!$this->isFileExist($path)) throw new FileNotFoundException($path);

        try {
            $ret = file_get_contents($checkedPath);
            if ($ret === false) throw new OperationFailedException();
            return static::wrapData($ret);
        } catch (Exception $ex) {
            throw new FileOperationFailedException($path, _l('read'), previous: $ex);
        }
    }


    /**
     * @inheritDoc
     */
    public function writeFile(string $path, BinaryDataProvidable|string $data, array $options = []) : void
    {
        $checkedPath = $this->checkPath($path);
        if ($checkedPath === null) throw new FileNotFoundException($path);

        $data = $data instanceof BinaryDataProvidable ? $data->getData() : $data;

        try {
            $ret = file_put_contents($checkedPath, $data);
            if ($ret === false) throw new OperationFailedException();
        } catch (Exception $ex) {
            throw new FileOperationFailedException($path, _l('write'), previous: $ex);
        }
    }


    /**
     * @inheritDoc
     */
    public function deleteFile(string $path) : bool
    {
        $path = $this->checkPath($path);
        if ($path === null) return false;

        if (!$this->isFileExist($path)) return false;

        return unlink($path);
    }


    /**
     * @inheritDoc
     */
    public function isDirectoryExist(string $path) : bool
    {
        $path = $this->checkPath($path);
        if ($path === null) return false;

        return static::isNativeDirectoryExist($path);
    }


    /**
     * @inheritDoc
     */
    public function createDirectory(string $path) : bool
    {
        $checkedPath = $this->checkPath($path);
        if ($checkedPath === null) throw new FileNotFoundException($path);

        return $this->createRecursiveDirectory($checkedPath);
    }


    /**
     * Create directory recursively
     * @param string $path
     * @return bool
     * @throws FileNotFoundException
     */
    protected function createRecursiveDirectory(string $path) : bool
    {
        // Cleanup
        if (str_ends_with('/', $path)) $path = substr($path, 0, -1);

        // Explicitly block bad condition
        if ($path === '') throw new FileNotFoundException($path);

        // Or when directory already exist
        if (static::isNativeDirectoryExist($path)) return false;

        // Filter invalid path to be created
        $currentPath = basename($path);
        if ($currentPath === '.' || $currentPath === '..') throw new FileNotFoundException($path);

        // Ensure parent created
        $parentPath = dirname($path);
        $this->createRecursiveDirectory($parentPath);

        // And then create current directory
        mkdir($path);

        return true;
    }


    /**
     * If directory exist (natively)
     * @param string $path
     * @return bool
     */
    private static function isNativeDirectoryExist(string $path) : bool
    {
        if (!file_exists($path)) return false;
        if (!is_dir($path)) return false;

        return true;
    }


    /**
     * @inheritDoc
     */
    public function deleteDirectory(string $path) : bool
    {
        $path = $this->checkPath($path);
        if ($path === null) return false;

        if (!$this->isDirectoryExist($path)) return false;

        return rmdir($path);
    }


    /**
     * @inheritDoc
     */
    protected function checkPath(string $path) : ?string
    {
        $checkedPath = parent::checkPath($path);
        if ($checkedPath === null) return null;

        $ret = $this->config->rootPath;
        if (str_ends_with($ret, '/')) $ret = substr($ret, 0, -1);
        if (!str_starts_with($checkedPath, '/')) $checkedPath = '/' . $checkedPath;

        return $ret . $checkedPath;
    }


    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return static::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function specificInitialize(FileSystemConfig $config) : static
    {
        if (!$config instanceof LocalFileSystemConfig) throw new NotOfTypeException($config, LocalFileSystemConfig::class);

        return new static($config);
    }


    /**
     * @inheritDoc
     */
    public static function systemBootRegister(BootRegistrar $registrar) : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public static function systemBoot(BootContext $context) : void
    {
        ClassFactory::includeDirectory(__DIR__);
    }
}