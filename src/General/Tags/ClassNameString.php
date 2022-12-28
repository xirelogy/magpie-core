<?php

namespace Magpie\General\Tags;

/**
 * Tag a string that is a class name
 */
final class ClassNameString
{
    /**
     * @var string Class name value
     */
    public string $className;


    /**
     * Constructor
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }
}