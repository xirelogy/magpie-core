<?php

namespace Magpie\Models;

/**
 * Raw query statement
 */
class RawStatement
{
    /**
     * @var string Main query statement
     */
    public string $sql;
    /**
     * @var array Bind arguments
     */
    public array $values;


    /**
     * Constructor
     * @param string $sql
     * @param array $values
     */
    public function __construct(string $sql, array $values = [])
    {
        $this->sql = $sql;
        $this->values = $values;
    }
}