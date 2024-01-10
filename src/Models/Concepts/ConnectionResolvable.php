<?php

namespace Magpie\Models\Concepts;

use Magpie\Exceptions\SafetyCommonException;
use Magpie\Models\Connection;
use Magpie\Models\Schemas\Configs\SchemaPreference;

/**
 * May resolve a database connection
 */
interface ConnectionResolvable
{
    /**
     * Resolve the connection
     * @return Connection
     * @throws SafetyCommonException
     */
    public function resolve() : Connection;


    /**
     * Resolve for the schema preference
     * @return SchemaPreference
     */
    public function getSchemaPreference() : SchemaPreference;
}