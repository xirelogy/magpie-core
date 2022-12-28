<?php

namespace Magpie\System\HardCore;

use Magpie\General\Traits\SingletonInstance;
use Magpie\System\Kernel\Kernel;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * Perform reflection using autoload map
 */
class AutoloadReflection
{
    use SingletonInstance;


    /**
     * @var bool If resolving the 'autoload map' had been attempted
     */
    protected bool $isResolved = false;
    /**
     * @var array<string, array<string>>|null Autoload map
     */
    protected ?array $map = null;


    /**
     * Discover all PHP source file in the given path(s)
     * @param string|array<string> $pathSpec
     * @return iterable<SplFileInfo>
     */
    public function expandDiscoverySources(string|array $pathSpec) : iterable
    {
        /** @var array<string> $paths */
        $paths = is_string($pathSpec) ? [$pathSpec] : $pathSpec;

        // Safety protection
        if (count($paths) <= 0) return;

        yield from (new Finder())->files()->name('*.php')->in($paths);
    }


    /**
     * Discover all PHP class reflection in the given path(s)
     * @param string|array $pathSpec
     * @param bool $isConcreteOnly Exclude non-concrete (abstract) classes when set to true
     * @return iterable<ReflectionClass>
     * @throws ReflectionException
     */
    public function expandDiscoverySourcesReflection(string|array $pathSpec, bool $isConcreteOnly = true) : iterable
    {
        foreach ($this->expandDiscoverySources($pathSpec) as $source) {
            $realPath = $source->getRealPath();
            if ($realPath === false) continue;

            $className = $this->getFullQualifiedClassNameFromFilename($realPath);
            if ($className === null) continue;

            $class = new ReflectionClass($className);
            if ($isConcreteOnly && $class->isAbstract()) continue;

            yield $class;
        }
    }


    /**
     * All filenames for given class
     * @param string $className
     * @return array<string>
     */
    public function getClassFilenames(string $className) : array
    {
        $ret = [];

        $map = $this->resolveAutoloadMap();
        if ($map === null) return $ret;

        foreach ($map as $key => $values) {
            if (str_starts_with($className, $key)) {
                // Process the path, convert the path separators
                $path = substr($className, strlen($key));
                $path = str_replace('\\', '/', $path);
                if (!str_starts_with($path, '/')) $path = '/' . $path;

                // Process the values
                foreach ($values as $value) {
                    $value = str_replace('\\', '/', $value);
                    while (str_ends_with($value, '/')) {
                        $value = substr($value, strlen($value) - 1);
                    }

                    $ret[] = $value . $path . '.php';
                }
            }
        }

        return $ret;
    }


    /**
     * Get the fully qualified class name from given filename
     * @param string $realPath
     * @return string|null
     */
    public function getFullQualifiedClassNameFromFilename(string $realPath) : ?string
    {
        $rootPath = static::resolveRootPath();
        if ($rootPath === null) return null;

        $rootPath = realpath($rootPath);
        if ($rootPath === false) return null;
        if (!str_starts_with($realPath, $rootPath)) return null;

        $classPath = null;
        $namespace = $this->getNamespacePrefix($realPath, $classPath);

        // Process the namespace and class paths
        if (!str_ends_with($namespace, '\\')) $namespace .= '\\';
        if (str_ends_with($classPath, '.php')) $classPath = substr($classPath, 0, -4);
        $classPath = str_replace([DIRECTORY_SEPARATOR], ['\\'], $classPath);
        if (str_starts_with($classPath, '\\')) $classPath = substr($classPath, 1);

        // Concatenate and return
        return $namespace . $classPath;
    }


    /**
     * Get namespace prefix
     * @param string $realPath Target file's real path
     * @param string|null $outRelPath The relative path after extracting namespace prefix
     * @return string Namespace prefix extracted
     */
    protected function getNamespacePrefix(string $realPath, ?string &$outRelPath) : string
    {
        $map = $this->resolveAutoloadMap();

        foreach ($map ?? [] as $key => $values) {
            foreach ($values as $value) {
                if (str_starts_with($realPath, $value)) {
                    $outRelPath = substr($realPath, strlen($value));
                    return $key;
                }
            }
        }

        $outRelPath = $realPath;
        return '';
    }


    /**
     * Try to resolve for the 'autoload map'
     * @return array<string, array<string>>|null
     */
    protected function resolveAutoloadMap() : ?array
    {
        if (!$this->isResolved) {
            $this->isResolved = true;

            $rootPath = realpath(static::resolveRootPath());
            if (empty($rootPath)) return null;

            $autoloadPath = $rootPath . '/vendor/composer/autoload_psr4.php';
            if (!file_exists($autoloadPath)) return null;

            $this->map = require $autoloadPath;
        }

        return $this->map;
    }


    /**
     * Try to resolve for the project's root path
     * @return string|null
     */
    protected static function resolveRootPath() : ?string
    {
        // Prefer the project's root path
        if (Kernel::hasCurrent()) {
            return Kernel::current()->projectPath;
        }

        // May try to take from 'PWD'
        if (array_key_exists('PWD', $_SERVER)) {
            return $_SERVER['PWD'];
        }

        // Not supported
        return null;
    }
}