<?php

namespace Magpie\Configurations;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter\AdapterInterface;
use Dotenv\Repository\Adapter\EnvConstAdapter;
use Dotenv\Repository\RepositoryBuilder;
use Dotenv\Repository\RepositoryInterface;
use Magpie\General\Traits\StaticClass;

/**
 * Support for environment variables (.env files)
 */
class Env
{
    use StaticClass;


    /**
     * @var RepositoryInterface|null Current repository
     */
    protected static ?RepositoryInterface $repository = null;
    /**
     * @var string|null Specific env file to be used
     */
    protected static ?string $usingEnvFilename = null;


    /**
     * Gets value from the environment
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null) : mixed
    {
        $value = static::getRepository()->get($key);
        if ($value === null) return $default;

        return static::translateValue($value);
    }


    /**
     * Translate environment value according to parsing rules
     * @param string|null $value
     * @return mixed
     */
    protected static function translateValue(?string $value) : mixed
    {
        if ($value === null) return null;

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return null;

            default:
                if (preg_match('/\A([\'"])(.*)\1\z/', $value, $matches)) {
                    return $matches[2];
                }

                return $value;
        }
    }


    /**
     * Specified the env file to be preferred
     * @param string $filename
     * @return void
     */
    public static function usingEnv(string $filename) : void
    {
        static::$usingEnvFilename = $filename;
    }


    /**
     * Boot up using given project directory
     * @param string $projectPath
     * @return void
     * @internal
     */
    public static function _boot(string $projectPath) : void
    {
        $repository = static::getRepository();
        $names = null;

        // Establish the base path for env files
        $envFilename = $projectPath;
        if (!str_ends_with($envFilename, '/')) $envFilename .= '/';

        if (static::$usingEnvFilename !== null && file_exists($envFilename . static::$usingEnvFilename)) {
            // Use the specific environment file if found
            $names = [static::$usingEnvFilename];
        } else {
            // Use .env.example when .env does not exist
            $envFilename .= '.env';
            if (!file_exists($envFilename)) $names = ['.env.example'];
        }

        $dotEnv = Dotenv::create($repository, $projectPath, $names);
        $dotEnv->load();
    }


    /**
     * Get the current repository instance
     * @return RepositoryInterface
     */
    protected static function getRepository() : RepositoryInterface
    {
        if (static::$repository === null) {
            $builder = RepositoryBuilder::createWithNoAdapters();

            /** @var AdapterInterface $adapter */
            foreach (static::getRepositoryAdapters() as $adapter) {
                $instance = $adapter::create();
                if ($instance->isDefined()) {
                    $builder = $builder->addAdapter($instance->get());
                }
            }

            static::$repository = $builder->immutable()->make();
        }

        return static::$repository;
    }


    /**
     * All repository adapters
     * @return iterable<class-string<AdapterInterface>>
     */
    protected static function getRepositoryAdapters() : iterable
    {
        yield EnvConstAdapter::class;
    }
}