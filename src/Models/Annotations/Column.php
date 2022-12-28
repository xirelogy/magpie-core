<?php

namespace Magpie\Models\Annotations;

use Attribute;

/**
 * Define database table's column
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class Column
{
    /**
     * Default attribute (nothing set)
     */
    public const ATTR_DEFAULT = 0;
    /**
     * Column is a primary key
     */
    public const ATTR_PRIMARY_KEY = 1;
    /**
     * Column value must be unique
     */
    public const ATTR_UNIQUE = 2;
    /**
     * Column is auto-incremental
     */
    public const ATTR_AUTO_INCREMENT = 8;
    /**
     * Column is bind to a 'created timestamp' function
     */
    public const ATTR_FN_CREATED = 2048;
    /**
     * Column is bind to a 'updated timestamp' function
     */
    public const ATTR_FN_UPDATED = 4096;
    /**
     * Default value specified as a value
     */
    public const DEFAULT_VALUE = 'value';
    /**
     * Default value specified as an expression
     */
    public const DEFAULT_EXPR = 'expr';

    /**
     * @var string Column name
     */
    public string $name;
    /**
     * @var string Column type definition
     */
    public string $def;
    /**
     * @var bool If column may not be null
     */
    public bool $isNonNull;
    /**
     * @var int Column attributes
     */
    public int $attrs;
    /**
     * @var string|null Column cast class (if not inferred by column type)
     */
    public ?string $cast;
    /**
     * @var string|null Column initializer class (if not inferred by column type)
     */
    public ?string $init;
    /**
     * @var array|string|int|float|bool|null Column's default value on database level
     */
    public array|string|int|float|bool|null $defaultValue;
    /**
     * @var string|null Foreign key model class name
     */
    public ?string $foreignModel;
    /**
     * @var string|null Foreign key model column name
     */
    public ?string $foreignColumn;
    /**
     * @var string|null Column comments
     */
    public ?string $comments;


    /**
     * Constructor
     * @param string $name Column name
     * @param string $def Column type definition
     * @param bool $isNonNull If column may not be null
     * @param int $attrs Column attributes
     * @param string|null $cast Column cast class (if not inferred by column type)
     * @param string|null $init Column initializer class (if not inferred by column type)
     * @param array|string|int|float|bool|null $defaultValue Column's default value on database level
     * @param string|null $foreignModel Foreign key model class name
     * @param string|null $foreignColumn Foreign key model column name
     * @param string|null $comments Column comments
     */
    public function __construct(
        string $name,
        string $def,
        bool $isNonNull = false,
        int $attrs = self::ATTR_DEFAULT,
        ?string $cast = null,
        ?string $init = null,
        array|string|int|float|bool|null $defaultValue = null,
        ?string $foreignModel = null,
        ?string $foreignColumn = null,
        ?string $comments = null,
    ) {
        $this->name = $name;
        $this->def = $def;
        $this->isNonNull = $isNonNull;
        $this->attrs = $attrs;
        $this->cast = $cast;
        $this->init = $init;
        $this->defaultValue = $defaultValue;
        $this->foreignModel = $foreignModel;
        $this->foreignColumn = $foreignColumn;
        $this->comments = $comments;
    }
}