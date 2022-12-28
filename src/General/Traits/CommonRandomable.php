<?php

namespace Magpie\General\Traits;

/**
 * Common randomable implementation
 */
trait CommonRandomable
{
    /**
     * Generate a random integer within the given numeric range
     * @param int $min
     * @param int|null $max
     * @return int
     */
    public abstract function integer(int $min = 0, ?int $max = null) : int;


    /**
     * Generate a random string of given length, picking from given character set
     * @param int $length
     * @param string $charset
     * @return string
     */
    public function string(int $length, string $charset) : string
    {
        $numChars = strlen($charset);

        $ret = '';
        for ($i = 0; $i < $length; ++$i) {
            $r = $this->integer(0, $numChars - 1);
            $ret .= substr($charset, $r, 1);
        }

        return $ret;
    }


    /**
     * Generate a random bytes string of given length
     * @param int $length
     * @return string
     */
    public function bytes(int $length) : string
    {
        $ret = '';
        for ($i = 0; $i < $length; ++$i) {
            $ret .= chr($this->integer(0, 255));
        }

        return $ret;
    }
}