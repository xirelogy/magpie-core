<?php

namespace Magpie\Models\Providers\Mysql;

use Magpie\General\Factories\Annotations\FactoryTypeClass;
use Magpie\Models\Configs\ConnectionConfig;
use Magpie\Models\Configs\DbmsConnectionConfig;

/**
 * MySQL specific connection configuration
 */
#[FactoryTypeClass(MysqlConnection::TYPECLASS, ConnectionConfig::class)]
class MysqlConnectionConfig extends DbmsConnectionConfig
{
    /**
     * @inheritDoc
     */
    public static function getTypeClass() : string
    {
        return MysqlConnection::TYPECLASS;
    }


    /**
     * @inheritDoc
     */
    protected static function envDefaultPort() : ?int
    {
        return 3306;
    }


    /**
     * @inheritDoc
     */
    protected static function envDefaultCharset() : ?string
    {
        return 'utf8mb4';
    }
}