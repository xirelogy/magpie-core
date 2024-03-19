<?php

namespace Magpie\System\HardCore;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Facades\FileSystem\FileSystem;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Simples\SimpleJSON;
use Magpie\General\Traits\SingletonInstance;
use Magpie\System\Concepts\AutoloadReflectionPathResolvable;
use Magpie\System\HardCore\AutoloadResolvers\AutoloadLinkPathResolver;
use Magpie\System\Kernel\ExceptionHandler;
use Magpie\System\Kernel\Kernel;
use ReflectionClass;
use ReflectionException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Throwable;

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
     * @var array<AutoloadReflectionPathResolvable> Path resolvers
     */
    protected array $pathResolvers = [];


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

        $this->onResolvePath($rootPath, $realPath);
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
     * Resolve paths before resolving namespace
     * @param string $rootPath
     * @param string $realPath
     * @return void
     */
    protected function onResolvePath(string $rootPath, string& $realPath) : void
    {
        foreach ($this->pathResolvers as $pathResolver) {
            $pathResolver->tryResolvePath($rootPath, $realPath);
        }
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


    /**
     * Automatically discover path resolvers from given composer repository files, and
     * add them automatically to the path resolvers (like addPathResolver)
     * @return $this
     */
    public function discoverPathResolversFromComposerRepositories() : static
    {
        try {
            $this->tryDiscoverPathResolversFromComposerRepositories();
        } catch (Throwable $ex) {
            ExceptionHandler::systemCritical($ex);
        }

        return $this;
    }


    /**
     * Try to discover for path resolvers from the project's root composer.json
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    private function tryDiscoverPathResolversFromComposerRepositories() : void
    {
        $composerPath = project_path('composer.json');
        $rootFs = LocalRootFileSystem::instance();
        if (!$rootFs->isFileExist($composerPath)) return;

        $data = SimpleJSON::decode($rootFs->readFile($composerPath)->getData());
        if (!isset($data->repositories)) return;

        $projectBasePath = project_path('/');
        if (!str_ends_with($projectBasePath, '/')) $projectBasePath = "$projectBasePath/";

        foreach ($data->repositories as $repository) {
            $this->tryDiscoverPathResolversFromComposerRepositorySpec($rootFs, $projectBasePath, $repository);
        }
    }


    /**
     * Try to discover for path resolvers from the composer.json of a specific repository specification
     * @param FileSystem $rootFs
     * @param string $projectBasePath
     * @param object $repository
     * @return void
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    private function tryDiscoverPathResolversFromComposerRepositorySpec(FileSystem $rootFs, string $projectBasePath, object $repository) : void
    {
        if (($repository->type ?? null) !== 'path') return;
        if (!isset($repository->url)) return;

        $repositoryUrl = $repository->url;

        $repositoryComposerPath = $projectBasePath . $repositoryUrl;
        if (!str_ends_with($repositoryComposerPath, '/')) $repositoryComposerPath = "$repositoryComposerPath/";
        $repositoryComposerPath .= 'composer.json';

        if (!$rootFs->isFileExist($repositoryComposerPath)) return;

        $data = SimpleJSON::decode($rootFs->readFile($repositoryComposerPath)->getData());
        if (!isset($data->name)) return;

        $projectName = $data->name;
        $vendorPath = "vendor/$projectName";

        if (!$rootFs->isDirectoryExist($projectBasePath . $vendorPath)) return;
        $this->addPathResolver(new AutoloadLinkPathResolver($vendorPath, $repositoryUrl));
    }


    public function debug() : never
    {
        dd($this);
    }


    /**
     * Add path resolver
     * @param AutoloadReflectionPathResolvable $resolver
     * @return $this
     */
    public function addPathResolver(AutoloadReflectionPathResolvable $resolver) : static
    {
        $this->pathResolvers[] = $resolver;
        return $this;
    }
}