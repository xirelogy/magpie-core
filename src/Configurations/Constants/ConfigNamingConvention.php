<?php

namespace Magpie\Configurations\Constants;

/**
 * Naming convention for configuration key names
 */
enum ConfigNamingConvention : string
{
    /**
     * Lowercase kebab: 'example-text'
     */
    case LOWER_KEBAB = 'lower-kebab';
    /**
     * Uppercase kebab: 'EXAMPLE-TEXT'
     */
    case UPPER_KEBAB = 'upper-kebab';
    /**
     * Lowercase underscore: 'example_text'
     */
    case LOWER_SNAKE = 'lower-snake';
    /**
     * Uppercase underscore: 'EXAMPLE_TEXT'
     */
    case UPPER_SNAKE = 'upper-under';
    /**
     * Camel case: 'exampleText'
     */
    case CAMEL_CASE = 'camel-case';
    /**
     * Pascal case: 'ExampleText'
     */
    case PASCAL_CASE = 'pascal-case';
}