<?php

namespace Magpie\System\HardCore;

use Exception;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Sugars\Excepts;
use Magpie\General\Traits\SingletonInstance;
use Magpie\System\Kernel\ExceptionHandler;

/**
 * Source caching service
 */
class SourceCache
{
    use SingletonInstance;

    protected readonly string $basePath;


    /**
     * Constructor
     * @param string $basePath
     * @throws Exception
     */
    protected function __construct(string $basePath)
    {
        if (!str_ends_with($basePath, '/')) $basePath .= '/';

        $this->basePath = $basePath;

        LocalRootFileSystem::instance()->createDirectory($basePath);
    }


    /**
     * Get cache
     * @param string $className
     * @return mixed
     */
    public function getCache(string $className) : mixed
    {
        $filename = $this->getCacheFilename($className);
        if (!LocalRootFileSystem::instance()->isFileExist($filename)) return null;

        return include $filename;
    }


    /**
     * Set cache
     * @param string $className
     * @param mixed $data
     * @return void
     * @throws Exception
     */
    public function setCache(string $className, mixed $data) : void
    {
        $filename = $this->getCacheFilename($className);
        $content = "<?php\nreturn " . var_export($data, true) . ";\n";

        LocalRootFileSystem::instance()->writeFile($filename, $content);
    }


    /**
     * Delete any existing cache
     * @param string $className
     * @return void
     */
    public function deleteCache(string $className) : void
    {
        $filename = $this->getCacheFilename($className);
        Excepts::noThrow(fn() => LocalRootFileSystem::instance()->deleteFile($filename));
    }


    /**
     * Get cache filename for corresponding class
     * @param string $className
     * @return string
     */
    protected function getCacheFilename(string $className) : string
    {
        $cleanedClassName = str_replace('\\', '-', $className);

        return $this->basePath . "SourceCache---$cleanedClassName.php";
    }


    /**
     * @inheritDoc
     */
    protected static function createInstance() : static
    {
        $basePath = project_path('/storage/caches/magpie');

        try {
            return new static($basePath);
        } catch (Exception $ex) {
            ExceptionHandler::systemCritical($ex);
        }
    }
}