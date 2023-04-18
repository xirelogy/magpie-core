<?php

namespace Magpie\Facades\FileSystem\Providers\Local;

use Magpie\Exceptions\FileNotFoundException;
use Magpie\Exceptions\OperationFailedException;
use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\General\Concepts\TargetWritable;
use Magpie\General\Traits\SingletonInstance;

/**
 * A local temporary file system
 */
class LocalTemporaryFileSystem extends LocalFileSystem
{
    use SingletonInstance;


    /**
     * @var string Root path
     */
    protected readonly string $rootPath;


    /**
     * Constructor
     */
    protected function __construct()
    {
        $rootPath = env('TEMP_DIR_PATH', sys_get_temp_dir());

        parent::__construct(new LocalFileSystemConfig($rootPath));

        $this->rootPath = $rootPath;
    }


    /**
     * Create a new file
     * @param string|null $prefix
     * @param string|null $extension
     * @return string
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public function createFile(?string $prefix = null, ?string $extension = null) : string
    {
        $filename = $this->createFilename($prefix, $extension);

        $this->writeFile($filename, '');

        return $filename;
    }


    /**
     * Create a new filename
     * @param string|null $prefix
     * @param string|null $extension
     * @return string
     * @throws SafetyCommonException
     */
    public function createFilename(?string $prefix = null, ?string $extension = null) : string
    {
        $prefix = $prefix ?? 'tmp';
        if (!str_ends_with($prefix, '_')) $prefix .= '_';

        $ret = @tempnam($this->rootPath, $prefix);
        if ($ret === false) throw new OperationFailedException();

        @unlink($ret);

        if ($extension !== null) $ret .= '.' . $extension;

        return $ret;
    }


    /**
     * @inheritDoc
     */
    public function writeFile(string $path, BinaryDataProvidable|string $data, array $options = []) : void
    {
        $scope = static::createUmaskScoped();
        _used($scope);

        parent::writeFile($path, $data, $options);
    }


    /**
     * @inheritDoc
     */
    public function writeTo(string $path) : TargetWritable
    {
        $checkedPath = $this->checkPath($path);
        if ($checkedPath === null) throw new FileNotFoundException($path);

        return new LocalFileWriteTarget($checkedPath, function () {
            yield static::createUmaskScoped();
        });
    }


    /**
     * @inheritDoc
     */
    public function createDirectory(string $path) : bool
    {
        $scope = static::createUmaskScoped();
        _used($scope);

        return parent::createDirectory($path);
    }


    /**
     * Create the corresponding umask scope
     * @return LocalUmaskScoped
     */
    protected static function createUmaskScoped() : LocalUmaskScoped
    {
        return LocalUmaskScoped::create(0);
    }


    /**
     * @inheritDoc
     */
    protected function checkPath(string $path) : ?string
    {
        $checkedPath = $this->defaultCheckPath($path);
        if ($checkedPath === null) return null;

        if (str_starts_with($checkedPath, '/')) return $checkedPath;

        $ret = $this->config->rootPath;
        if (str_ends_with($ret, '/')) $ret = substr($ret, 0, -1);
        if (!str_starts_with($checkedPath, '/')) $checkedPath = '/' . $checkedPath;

        return $ret . $checkedPath;
    }
}