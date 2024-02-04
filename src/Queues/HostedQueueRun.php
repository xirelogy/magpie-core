<?php

namespace Magpie\Queues;

use Magpie\Exceptions\PersistenceException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\StreamException;
use Magpie\Facades\FileSystem\Providers\Local\LocalTemporaryFileSystem;
use Magpie\System\Kernel\Kernel;
use Magpie\System\Process\Process;
use Magpie\System\Process\ProcessCommandLine;

/**
 * Host a queue runnable in specific environment and individual process
 */
class HostedQueueRun
{
    /**
     * @var BaseQueueRunnable Target running object
     */
    protected readonly BaseQueueRunnable $target;
    /**
     * @var string|null Specific vendor autoload path
     */
    protected ?string $vendorAutoloadPath = null;
    /**
     * @var string|null Root path
     */
    protected ?string $rootPath = null;
    /**
     * @var string|null Application config path
     */
    protected ?string $appConfigPath = null;
    /**
     * @var string|null Specific 'use-env'
     */
    protected ?string $useEnv = null;


    /**
     * Target running class
     * @param BaseQueueRunnable $target
     */
    protected function __construct(BaseQueueRunnable $target)
    {
        $this->target = $target;
    }


    /**
     * Set the specific vendor autoload path
     * @param string $path
     * @return $this
     */
    public function withVendorAutoloadPath(string $path) : static
    {
        $this->vendorAutoloadPath = $path;
        return $this;
    }


    /**
     * Set the specific root path
     * @param string $path
     * @return $this
     */
    public function withRootPath(string $path) : static
    {
        $this->rootPath = $path;
        return $this;
    }


    /**
     * Set the specific application config path
     * @param string $path
     * @return $this
     */
    public function withAppConfigPath(string $path) : static
    {
        $this->appConfigPath = $path;
        return $this;
    }


    /**
     * Set the specific environment specification to be used (overridden)
     * @param string $env
     * @return $this
     */
    public function withUseEnv(string $env) : static
    {
        $this->useEnv = $env;
        return $this;
    }


    /**
     * Create the process for running
     * @return Process
     * @throws SafetyCommonException
     * @throws PersistenceException
     * @throws StreamException
     */
    public function createProcess() : Process
    {
        $scriptFilename = realpath(__DIR__ . '/Impls/_host.php');
        $commandLine = ProcessCommandLine::fromPhp($scriptFilename);

        $vendorAutoloadPath = $this->vendorAutoloadPath ?? project_path('/vendor/autoload.php');
        $rootPath = $this->rootPath ?? Kernel::current()->projectPath;
        $appConfigPath = $this->appConfigPath ?? project_path('/boot/config.php');

        $fs = LocalTemporaryFileSystem::instance();
        $targetFilename = $fs->createFile('host');
        $fs->writeFile($targetFilename, serialize($this->target));

        $envs = [
            static::makeEnvKey('TARGET') => $targetFilename,
            static::makeEnvKey('VENDOR_AUTOLOAD') => $vendorAutoloadPath,
            static::makeEnvKey('ROOT') => $rootPath,
            static::makeEnvKey('APPCONFIG') => $appConfigPath,
        ];

        if ($this->useEnv !== null) {
            $envs[static::makeEnvKey('USE_ENV')] = $this->useEnv;
        }

        $commandLine->withEnvironment($envs);
        return Process::fromCommandLine($commandLine);
    }


    /**
     * Create an instance
     * @param BaseQueueRunnable $target
     * @return static
     */
    public static function create(BaseQueueRunnable $target) : static
    {
        return new static($target);
    }


    /**
     * Create environment key
     * @param string $key
     * @return string
     */
    protected static function makeEnvKey(string $key) : string
    {
        return 'MAGPIE_HOST_ENV_' . $key;
    }
}