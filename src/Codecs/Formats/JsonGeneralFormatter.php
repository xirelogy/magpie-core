<?php

namespace Magpie\Codecs\Formats;

use DateTimeInterface;

/**
 * The target must be converted to something that is friendly to JSON output.
 */
class JsonGeneralFormatter extends GeneralFormatter
{
    /**
     * @inheritDoc
     */
    public function format(mixed $value) : mixed
    {
        // Specific time instance is communicated as timestamp
        if ($value instanceof DateTimeInterface) {
            return $value->getTimestamp();
        }

        return parent::format($value);
    }
}