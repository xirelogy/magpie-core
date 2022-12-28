<?php

namespace Magpie\General\Concepts;

/**
 * May generate random data
 */
interface Randomable extends TypeClassable
{
    /**
     * Generate a random integer within the given numeric range
     * @param int $min
     * @param int|null $max
     * @return int
     */
    public function integer(int $min = 0, ?int $max = null) : int;


    /**
     * Generate a random string of given length, picking from given character set
     * @param int $length
     * @param string $charset
     * @return string
     */
    public function string(int $length, string $charset) : string;


    /**
     * Generate a random bytes string of given length
     * @param int $length
     * @return string
     */
    public function bytes(int $length) : string;
}