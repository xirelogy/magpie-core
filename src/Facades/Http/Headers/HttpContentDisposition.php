<?php

namespace Magpie\Facades\Http\Headers;

use Magpie\General\Traits\StaticClass;
use Stringable;

/**
 * HTTP content-disposition
 */
class HttpContentDisposition
{
    use StaticClass;


    /**
     * Try to decode for filename from content-disposition header
     * @param mixed $value
     * @return string|null
     */
    public static function decodeFilename(mixed $value) : ?string
    {
        if ($value === null) return null;
        if (!is_string($value) && !($value instanceof Stringable)) return null;

        $value = "$value";
        $keyValues = explode(';', $value);

        foreach ($keyValues as $keyValue) {
            $equalPos = strpos($keyValue, '=');
            if ($equalPos === false) continue;

            $inKey = trim(substr($keyValue, 0, $equalPos));
            $inValue = trim(substr($keyValue, $equalPos + 1));
            if ($inKey !== 'filename') continue;

            if (str_starts_with($inValue, '"') && str_ends_with($inValue, '"')) {
                $inValue = trim(substr($inValue, 1, strlen($inValue) - 2));
            }

            return $inValue;
        }

        return null;
    }
}