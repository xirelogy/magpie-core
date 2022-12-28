<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Exceptions\UnsupportedTypeClassException;
use Magpie\General\Traits\StaticClass;
use Magpie\Models\Connection;
use Magpie\System\Kernel\Kernel;

/**
 * Cache multiple connections
 * @internal
 */
class ConnectionsCache
{
    use StaticClass;

    /**
     * @var array<string, Connection> Mapped connections
     */
    protected static array $connections = [];


    /**
     * Get cached connection from given name
     * @param string $name
     * @return Connection
     * @throws SafetyCommonException
     */
    public static function fromName(string $name) : Connection
    {
        if (!array_key_exists($name, static::$connections)) {
            $config = Kernel::current()->getConfig()->getModelConnectionConfig($name);
            if ($config === null) throw new UnsupportedTypeClassException($name, _l('database connection'));
            static::$connections[$name] = Connection::initialize($config);
        }

        return static::$connections[$name];
    }
}