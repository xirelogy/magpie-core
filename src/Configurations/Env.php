<?php

namespace Magpie\Configurations;

use Dotenv\Dotenv;
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
     * Boot up using given project directory
     * @param string $projectPath
     * @return void
     * @internal
     */
    public static function _boot(string $projectPath) : void
    {
        $dotEnv = Dotenv::createImmutable($projectPath);
        $dotEnv->load();
    }


    /**
     * Get the current repository instance
     * @return RepositoryInterface
     */
    protected static function getRepository() : RepositoryInterface
    {
        if (static::$repository === null) {
            $builder = RepositoryBuilder::createWithDefaultAdapters();

            static::$repository = $builder->immutable()->make();
        }

        return static::$repository;
    }
}