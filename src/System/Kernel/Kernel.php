<?php

namespace Magpie\System\Kernel;

use Closure;
use Exception;
use Magpie\Configurations\AppConfig;
use Magpie\Configurations\Env;
use Magpie\Facades\FileSystem\Providers\Local\LocalRootFileSystem;
use Magpie\General\Concepts\TypeClassable;
use Magpie\General\Sugars\Excepts;
use Magpie\Logs\Concepts\Loggable;
use Magpie\Logs\Loggers\DefaultLogger;
use Magpie\Objects\NumericVersion;
use Magpie\Objects\Version;
use Magpie\System\Concepts\SystemBootable;
use Magpie\System\Concepts\SystemMaintainable;
use Magpie\System\DefaultBootables;
use Magpie\System\HardCore\FileBasedSystemMaintainer;
use Magpie\System\Impls\BootActionableRegistrar;
use Magpie\System\Impls\SymfonyVarDumperOptimizer;

/**
 * The main kernel
 */
final class Kernel
{
    /**
     * Major version
     */
    protected const VER_MAJOR = 0;
    /**
     * Minor version
     */
    protected const VER_MINOR = 1;
    /**
     * Release version
     */
    protected const VER_RELEASE = 704;


    /**
     * @var static|null Current instance
     */
    protected static ?self $instance = null;
    /**
     * @var string Project path
     */
    public readonly string $projectPath;
    /**
     * @var AppConfig Associated configuration
     */
    protected readonly AppConfig $config;
    /**
     * @var Loggable Default logger interface
     */
    protected Loggable $logger;
    /**
     * @var array<string, TypeClassable> Global current providers
     */
    protected array $globalProviders = [];
    /**
     * @var array<Closure> Listeners to react to termination
     */
    protected array $onTerminateListeners = [];


    /**
     * Constructor
     * @param string $projectPath
     * @param AppConfig $config
     */
    protected function __construct(string $projectPath, AppConfig $config)
    {
        $this->projectPath = $projectPath;
        $this->config = $config;

        $defaultRelay = $this->config->createDefaultLogRelay();
        $this->logger = new DefaultLogger($defaultRelay);

        static::$instance = $this;
    }


    /**
     * If system is under maintenance
     * @return bool
     */
    public function isUnderMaintenance() : bool
    {
        return $this->getSystemMaintainer()->isUnderMaintenance();
    }


    /**
     * Get current system maintenance interface
     * @return SystemMaintainable
     */
    public function getSystemMaintainer() : SystemMaintainable
    {
        $instance = $this->getProvider(SystemMaintainable::class);
        if ($instance instanceof SystemMaintainable) return $instance;

        return new FileBasedSystemMaintainer();
    }


    /**
     * Configuration
     * @return AppConfig
     */
    public function getConfig() : AppConfig
    {
        return $this->config;
    }


    /**
     * Get the logger interface
     * @return Loggable
     */
    public function getLogger() : Loggable
    {
        return $this->logger;
    }


    /**
     * Set the logger interface
     * @param Loggable $logger
     * @return $this
     */
    public function setLogger(Loggable $logger) : static
    {
        $this->logger = $logger;
        return $this;
    }


    /**
     * Find registered global providers
     * @param string $interfaceClassName
     * @return TypeClassable|null
     */
    public function getProvider(string $interfaceClassName) : ?TypeClassable
    {
        return $this->globalProviders[$interfaceClassName] ?? null;
    }


    /**
     * Register a global provider
     * @param string $interfaceClassName
     * @param TypeClassable $provider
     * @return void
     */
    public function registerProvider(string $interfaceClassName, TypeClassable $provider) : void
    {
        $this->globalProviders[$interfaceClassName] = $provider;
    }


    /**
     * Current kernel version
     * @return Version
     */
    public function getVersion() : Version
    {
        return NumericVersion::fromNumbers(static::VER_MAJOR, static::VER_MINOR, static::VER_RELEASE);
    }


    /**
     * Subscribe to termination
     * @param callable():void $fn
     * @return void
     */
    public function onTerminating(callable $fn) : void
    {
        $this->onTerminateListeners[] = $fn;
    }


    /**
     * Notify that context is destructing
     * @return void
     * @internal
     */
    public function _notifyContextDestructing() : void
    {
        foreach ($this->onTerminateListeners as $onTerminateListener) {
            Excepts::noThrow(fn () => $onTerminateListener());
        }
    }


    /**
     * Current kernel instance
     * @return static
     */
    public static function current() : static
    {
        if (static::$instance === null) ExceptionHandler::systemCritical('Kernel instance unavailable');

        return static::$instance;
    }


    /**
     * If current kernel instance is available
     * @return bool
     */
    public static function hasCurrent() : bool
    {
        return static::$instance !== null;
    }


    /**
     * Name of the entrance context
     * @return string
     */
    public static function getEntranceContext() : string
    {
        if (defined('MAGPIE_INTERNAL_CONTEXT')) return MAGPIE_INTERNAL_CONTEXT;

        return '';
    }


    /**
     * Boot up
     * @param string $projectPath
     * @param AppConfig $config
     * @return static
     */
    public static function boot(string $projectPath, AppConfig $config) : static
    {
        if (static::$instance !== null) ExceptionHandler::systemCritical('Kernel already booted up');

        SymfonyVarDumperOptimizer::setup();

        // Exception handlers to be required as soon as possible
        ExceptionHandler::_boot();

        // Real project path required
        $realProjectPath = static::getRealProjectPath($projectPath);

        // Environment boot up
        Env::_boot($realProjectPath);

        // Boot up all registered items
        $registrations = static::registerBoots($realProjectPath, $config);

        try {
            static::bootFromRegistrations($registrations);
        } catch (Exception $ex) {
            $bootFailureException = new Exception('Boot failure', previous: $ex);
            ExceptionHandler::systemCritical($bootFailureException);
        }

        // Kernel boot up successful
        $ret = new static($realProjectPath, $config);

        // Configuration may be then initialized
        $config->initialize($ret);

        return $ret;
    }


    /**
     * Register boot-ups
     * @param string $realProjectPath
     * @param AppConfig $config
     * @return array<string, BootActionableRegistrar>
     */
    protected static function registerBoots(string $realProjectPath, AppConfig $config) : array
    {
        $ret = [];

        foreach (static::getBootRegistrableClasses($realProjectPath, $config) as $class) {
            $registrar = BootActionableRegistrar::fromRegistration($class);
            if ($registrar === null) continue;

            if (!array_key_exists($registrar->className, $ret)) {
                $ret[$registrar->className] = $registrar;
            }

            foreach ($registrar->getSubRegistrars() as $subRegistrar) {
                if (!array_key_exists($subRegistrar->className, $ret)) {
                    $ret[$subRegistrar->className] = $subRegistrar;
                }
            }
        }

        return $ret;
    }


    /**
     * All boot registrable classes
     * @param string $realProjectPath
     * @param AppConfig $config
     * @return iterable<class-string<SystemBootable>>
     */
    protected static function getBootRegistrableClasses(string $realProjectPath, AppConfig $config) : iterable
    {
        // From Magpie defaults
        yield from DefaultBootables::getClasses();

        // From configurations
        yield from $config->getBootRegistrableClasses();

        // From discovered classes
        $bootDiscoveredClassesPath = $realProjectPath . '/boot/cache/discovered_classes.php';
        if (LocalRootFileSystem::instance()->isFileExist($bootDiscoveredClassesPath)) {
            yield from include $bootDiscoveredClassesPath;
        }

        // From explicit declaration
        $bootRegisteredClassesPath = $realProjectPath . '/boot/registered_classes.php';
        if (LocalRootFileSystem::instance()->isFileExist($bootRegisteredClassesPath)) {
            yield from include $bootRegisteredClassesPath;
        }
    }


    /**
     * Boot from registrations
     * @param array<BootActionableRegistrar> $registrations
     * @return void
     * @throws Exception
     */
    protected static function bootFromRegistrations(array $registrations) : void
    {
        /**
         * Create context
         */
        $context = new class() extends BootContext {
            /**
             * @var array<string, array<string>> Map of completed items
             */
            protected array $completedMap = [];


            /**
             * @inheritDoc
             */
            public function isBooted(string $className) : bool
            {
                return array_key_exists($className, $this->completedMap);
            }


            /**
             * Run boot up
             * @param BootActionableRegistrar $registration
             * @return bool
             * @throws Exception
             */
            public function runBoot(BootActionableRegistrar $registration) : bool
            {
                if (!$registration->runBoot($this)) return false;

                foreach ($registration->getBootProvides() as $provided) {
                    $boots = $this->completedMap[$provided] ?? [];
                    $boots[] = $registration->className;
                    $this->completedMap[$provided] = $boots;
                }

                return true;
            }
        };

        // Perform boot ups
        while (true) {
            $isPending = false;
            $hasSuccess = false;
            $totalCompleted = 0;

            foreach ($registrations as $registration) {
                if ($registration->isBooted()) {
                    ++$totalCompleted;
                    continue;
                }

                // There is still pending item
                $isPending = true;

                // Try to boot
                if (!$context->runBoot($registration)) continue;

                ++$totalCompleted;
                $hasSuccess = true;
            }

            // When there isn't any pending items, boot up is complete
            if (!$isPending) return;
            if ($totalCompleted >= count($registrations)) return;

            if (!$hasSuccess) throw new Exception('Boot up cannot continue because of unsatisfiable dependencies or loop');
        }
    }


    /**
     * Get the project's real path
     * @param string $projectPath
     * @return string
     */
    protected static function getRealProjectPath(string $projectPath) : string
    {
        $real = realpath($projectPath);
        return ($real !== false) ? $real : $projectPath;
    }
}