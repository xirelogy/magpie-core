<?php

namespace Magpie\Models\Impls;

use Magpie\Exceptions\InvalidDataException;
use Magpie\General\Traits\StaticClass;
use Magpie\Models\Casts\BooleanAttributeCast;
use Magpie\Models\Casts\DateAttributeCast;
use Magpie\Models\Casts\FloatAttributeCast;
use Magpie\Models\Casts\IntegerAttributeCast;
use Magpie\Models\Casts\JsonAttributeCast;
use Magpie\Models\Casts\StringAttributeCast;
use Magpie\Models\Casts\TimestampAttributeCast;
use Magpie\Models\Schemas\DataType;
use Magpie\Models\Schemas\ModelDefinition;

/**
 * Default data types
 * @internal
 */
class DefaultDataTypes
{
    use StaticClass;


    /**
     * Try to resolve alias model definitions
     * @param ModelDefinition $definition
     * @return ModelDefinition
     * @throws InvalidDataException
     */
    public static function resolveAlias(ModelDefinition $definition) : ModelDefinition
    {
        /** @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection */
        switch ($definition->baseType) {
            case 'boolean':
                return $definition->cloneWithBaseType('bool');
            default:
                return $definition;
        }
    }


    /**
     * Try to resolve default model definitions
     * @param ModelDefinition $definition
     * @return DataType|null
     */
    public static function resolve(ModelDefinition $definition) : ?DataType
    {
        /** @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection */
        switch ($definition->baseType) {
            case 'bigint':
                return new DataType('bigint', 'int', IntegerAttributeCast::class);
            case 'bool':
                return new DataType('bool', 'bool', BooleanAttributeCast::class);
            case 'char':
                return new DataType('char', 'string', StringAttributeCast::class);
            case 'date':
                return new DataType('date', 'SimpleDate', DateAttributeCast::class);
            case 'datetime':
                return new DataType('datetime', 'CarbonInterface', TimestampAttributeCast::class);
            case 'decimal':
                return new DataType('decimal', 'float', FloatAttributeCast::class);
            case 'double':
                return new DataType('double', 'float', FloatAttributeCast::class);
            case 'float':
                return new DataType('float', 'float', FloatAttributeCast::class);
            case 'int':
                return new DataType('int', 'int', IntegerAttributeCast::class);
            case 'json':
                return new DataType('text', 'mixed', JsonAttributeCast::class);
            case 'longtext':
                return new DataType('longtext', 'string', StringAttributeCast::class);
            case 'smallint':
                return new DataType('smallint', 'int', IntegerAttributeCast::class);
            case 'text':
                return new DataType('text', 'string', StringAttributeCast::class);
            case 'timestamp':
                return new DataType('timestamp', 'CarbonInterface', TimestampAttributeCast::class);
            case 'tinyint':
                return new DataType('tinyint', 'int', IntegerAttributeCast::class);
            case 'ubigint':
                return new DataType('ubigint', 'int', IntegerAttributeCast::class);
            case 'uint':
                return new DataType('uint', 'int', IntegerAttributeCast::class);
            case 'usmallint':
                return new DataType('usmallint', 'int', IntegerAttributeCast::class);
            case 'utinyint':
                return new DataType('utinyint', 'int', IntegerAttributeCast::class);
            case 'varchar':
                return new DataType('varchar', 'string', StringAttributeCast::class);
            default:
                return null;
        }
    }
}