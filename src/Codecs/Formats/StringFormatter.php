<?php

namespace Magpie\Codecs\Formats;

use Magpie\General\Traits\StaticCreatable;

/**
 * Format anything into string
 */
class StringFormatter implements Formatter
{
    use StaticCreatable;


    /**
     * @inheritDoc
     */
    public function format(mixed $value) : string
    {
        return "$value";
    }
}