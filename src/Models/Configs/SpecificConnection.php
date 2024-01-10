<?php

namespace Magpie\Models\Configs;

use Magpie\Models\Concepts\ConnectionResolvable;
use Magpie\Models\Connection;
use Magpie\Models\Schemas\Configs\SchemaPreference;

/**
 * A specific connection specification
 */
class SpecificConnection implements ConnectionResolvable
{
    /**
     * @var Connection The associated connection
     */
    protected readonly Connection $connection;
    /**
     * @var SchemaPreference The associated schema preference
     */
    protected readonly SchemaPreference $preference;


    /**
     * Constructor
     * @param Connection $connection
     * @param SchemaPreference|null $preference
     */
    protected function __construct(Connection $connection, ?SchemaPreference $preference)
    {
        $this->connection = $connection;
        $this->preference = $preference ?? SchemaPreference::default();
    }


    /**
     * @inheritDoc
     */
    public function resolve() : Connection
    {
        return $this->connection;
    }


    /**
     * @inheritDoc
     */
    public function getSchemaPreference() : SchemaPreference
    {
        return $this->preference;
    }


    /**
     * Create an instance
     * @param Connection $connection
     * @param SchemaPreference|null $preference
     * @return static
     */
    public static function create(Connection $connection, ?SchemaPreference $preference = null) : static
    {
        return new static($connection, $preference);
    }
}