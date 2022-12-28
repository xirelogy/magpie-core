<?php

namespace Magpie\Codecs\Formats;

/**
 * Apply formatting according to given format and convention
 */
interface Formatter
{
    /**
     * Format the given value
     * @param mixed $value
     * @return mixed
     */
    public function format(mixed $value) : mixed;
}